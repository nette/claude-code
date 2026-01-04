# RFC: Auto-discovery pro Nette DI Extensions

## Motivace

Aktuálně musí uživatel ručně registrovat každou DI extension v konfiguračním souboru:

```neon
extensions:
    console: Contributte\Console\DI\ConsoleExtension
    forms: Nette\Bridges\FormsDI\FormsExtension
    latte: Nette\Bridges\ApplicationDI\LatteExtension
```

To vede k:
- Boilerplate kódu při každé instalaci balíčku
- Chybám při kopírování názvů tříd
- Nutnosti číst dokumentaci každého balíčku

Jiné frameworky (Symfony Flex, Laravel Package Discovery) tento problém řeší automatickou registrací.

## Návrh

### 1. Deklarace v composer.json balíčku

Balíček deklaruje svou extension v `extra.nette`:

```json
{
    "name": "contributte/console",
    "extra": {
        "nette": {
            "extensions": {
                "console": "Contributte\\Console\\DI\\ConsoleExtension"
            }
        }
    }
}
```

Více extensions v jednom balíčku:

```json
{
    "extra": {
        "nette": {
            "extensions": {
                "console": "Contributte\\Console\\DI\\ConsoleExtension",
                "console.di": "Contributte\\Console\\DI\\DIExtension"
            }
        }
    }
}
```

### 2. Composer plugin

Nový balíček `nette/discovery` funguje jako Composer plugin:

- Reaguje na `post-install-cmd` a `post-update-cmd`
- Skenuje `installed.json` pro balíčky s `extra.nette`
- Generuje discovery cache soubor

```php
// vendor/nette/discovery/src/Plugin.php

class DiscoveryPlugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onInstall',
            ScriptEvents::POST_UPDATE_CMD => 'onInstall',
        ];
    }

    public function onInstall(Event $event): void
    {
        $extensions = [];
        $packages = $event->getComposer()->getRepositoryManager()
            ->getLocalRepository()->getPackages();

        foreach ($packages as $package) {
            $extra = $package->getExtra();
            if (isset($extra['nette']['extensions'])) {
                foreach ($extra['nette']['extensions'] as $name => $class) {
                    $extensions[$name] = [
                        'class' => $class,
                        'package' => $package->getName(),
                    ];
                }
            }
        }

        $this->writeCache($extensions);
    }

    private function writeCache(array $extensions): void
    {
        $content = "<?php\nreturn " . var_export($extensions, true) . ";\n";
        file_put_contents('vendor/nette-extensions.php', $content);
    }
}
```

### 3. Vygenerovaný soubor

```php
// vendor/nette-extensions.php (generovaný automaticky)

return [
    'console' => [
        'class' => 'Contributte\\Console\\DI\\ConsoleExtension',
        'package' => 'contributte/console',
    ],
    'translation' => [
        'class' => 'Contributte\\Translation\\DI\\TranslationExtension',
        'package' => 'contributte/translation',
    ],
];
```

### 4. Integrace do Configurator

```php
// nette/bootstrap/src/Configurator.php

class Configurator
{
    private bool $enableDiscovery = false;
    private array $disabledExtensions = [];

    /**
     * Povolí automatickou registraci discovered extensions.
     */
    public function enableDiscovery(): static
    {
        $this->enableDiscovery = true;
        return $this;
    }

    /**
     * Zakáže konkrétní discovered extension.
     */
    public function disableExtension(string $name): static
    {
        $this->disabledExtensions[] = $name;
        return $this;
    }

    public function createContainer(): Container
    {
        if ($this->enableDiscovery) {
            $this->loadDiscoveredExtensions();
        }
        // ... zbytek stávající logiky
    }

    private function loadDiscoveredExtensions(): void
    {
        $file = dirname(__DIR__, 4) . '/vendor/nette-extensions.php';
        if (!file_exists($file)) {
            return;
        }

        $extensions = require $file;
        foreach ($extensions as $name => $config) {
            if (in_array($name, $this->disabledExtensions, true)) {
                continue;
            }
            if (!isset($this->extensions[$name])) {
                $class = $config['class'];
                $this->addExtension($name, new $class);
            }
        }
    }
}
```

### 5. Použití v Bootstrap.php

```php
// Minimální změna pro zapnutí discovery
$configurator = new Configurator;
$configurator->enableDiscovery();
$configurator->addConfig(__DIR__ . '/config/common.neon');
$container = $configurator->createContainer();
```

S vypnutím konkrétní extension:

```php
$configurator = new Configurator;
$configurator->enableDiscovery();
$configurator->disableExtension('console');  // Nechci auto-registered console
$configurator->addConfig(__DIR__ . '/config/common.neon');
```

## Priorita a přepisování

### Pořadí registrace

1. **Discovered extensions** (nejnižší priorita)
2. **Manuálně registrované v PHP** (`$configurator->addExtension()`)
3. **Definované v NEON** (nejvyšší priorita)

### Přepsání discovered extension

```neon
# Úplné nahrazení vlastní implementací
extensions:
    console: My\Custom\ConsoleExtension

# Úplné vypnutí
extensions:
    console: false
```

### Konfigurace discovered extension

Funguje standardně:

```neon
console:
    url: /admin/console
    lazy: true
```

## Řešení konfliktů

### Dva balíčky se stejným jménem extension

```json
// balicek-a/composer.json
{ "extra": { "nette": { "extensions": { "api": "A\\ApiExtension" }}}}

// balicek-b/composer.json
{ "extra": { "nette": { "extensions": { "api": "B\\ApiExtension" }}}}
```

**Řešení:** Composer plugin vyhodí varování a použije poslední (abecedně).

Uživatel může konflikt vyřešit explicitně:

```neon
extensions:
    api: A\ApiExtension  # Explicitní volba
```

### Priorita balíčků

Volitelně lze definovat prioritu:

```json
{
    "extra": {
        "nette": {
            "extensions": {
                "database": "My\\DatabaseExtension"
            },
            "priority": -10
        }
    }
}
```

Nižší číslo = dřívější registrace. Výchozí je 0.

## Bezpečnost

### Ochrana proti škodlivým balíčkům

Discovery registruje pouze extension třídy - žádný kód se nespouští při instalaci.

Třída musí:
- Existovat v autoloadu
- Dědit z `Nette\DI\CompilerExtension`

Pokud třída neexistuje nebo není validní extension, vyhodí se výjimka při `createContainer()`.

## Migrace

### Pro uživatele

1. Přidat `"nette/discovery": "^1.0"` do require-dev
2. Přidat `$configurator->enableDiscovery()` do Bootstrap.php
3. Odstranit extensions z NEON (volitelné, pro čistotu)

### Pro autory balíčků

Přidat `extra.nette.extensions` do composer.json. Zpětně kompatibilní - balíček funguje i bez discovery.

## Alternativy

### A) Bez Composer pluginu

Discovery by se dělo při každém `createContainer()`:

```php
private function loadDiscoveredExtensions(): void
{
    $installed = json_decode(
        file_get_contents('vendor/composer/installed.json'),
        true
    );
    // ... parse extensions
}
```

**Nevýhoda:** Pomalejší, parsuje JSON při každém requestu (v dev módu).

### B) Atributy na třídách

```php
#[Extension('console')]
class ConsoleExtension extends CompilerExtension {}
```

**Nevýhoda:** Vyžaduje skenování všech tříd, velmi pomalé.

### C) Discovery soubor v balíčku

```
vendor/contributte/console/.nette-extension
```

**Nevýhoda:** Další soubor k údržbě, glob skenování vendor/.

## Implementační plán

1. **nette/discovery** - Composer plugin (nový balíček)
2. **nette/bootstrap** - Přidat `enableDiscovery()` metodu
3. **Dokumentace** - Návod pro autory balíčků
4. **Contributte migrace** - Přidat extra.nette do hlavních balíčků

## Otevřené otázky

1. Má se discovery zapínat automaticky, nebo explicitně?
2. Kam ukládat cache soubor? (`vendor/` vs `temp/`)
3. Má plugin zobrazovat přehled discovered extensions?
