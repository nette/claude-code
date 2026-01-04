# Nette Web Project Skeleton

Standard structure for new Nette projects. Create via `composer create-project nette/web-project`.

## Directory Structure

```
project/
├── app/                        # Application code
│   ├── Bootstrap.php           # Application initialization
│   ├── Core/                   # Infrastructure
│   │   └── RouterFactory.php
│   └── Presentation/           # UI layer
│       ├── @layout.latte       # Shared layout template
│       ├── Accessory/          # Shared UI components
│       │   └── LatteExtension.php
│       ├── Home/               # Homepage presenter
│       │   ├── HomePresenter.php
│       │   └── default.latte
│       └── Error/              # Optional Error handling presenters
├── assets/                     # Frontend source files
├── bin/                        # CLI scripts
├── config/                     # Configuration
│   ├── common.neon             # Framework config
│   └── services.neon           # Service definitions
├── log/                        # Log files (gitignored)
├── temp/                       # Cache/temp (gitignored)
├── tests/                      # Test files
│   └── bootstrap.php
├── www/                        # Public web root
│   ├── index.php               # Entry point
│   ├── .htaccess
│   └── robots.txt
├── composer.json
└── phpstan.neon
```

## Entry Point (www/index.php)

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new App\Bootstrap;
$container = $bootstrap->bootWebApplication();
$application = $container->getByType(Nette\Application\Application::class);
$application->run();
```

## Bootstrap (app/Bootstrap.php)

```php
<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;


class Bootstrap
{
	private readonly Configurator $configurator;
	private readonly string $rootDir;


	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator;
		$this->configurator->setTempDirectory($this->rootDir . '/temp');
	}


	public function bootWebApplication(): Nette\DI\Container
	{
		$this->initializeEnvironment();
		$this->setupContainer();
		return $this->configurator->createContainer();
	}


	public function initializeEnvironment(): void
	{
		$this->configurator->enableTracy($this->rootDir . '/log');

		$this->configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();
	}


	private function setupContainer(): void
	{
		$configDir = $this->rootDir . '/config';
		$this->configurator->addConfig($configDir . '/common.neon');
		$this->configurator->addConfig($configDir . '/services.neon');
		// ...
	}
}
```

## Configuration Files

```
config/
├── common.neon         # Shared framework config
├── services.neon       # Service definitions
├── env.local.neon      # Local development (gitignored)
└── env.prod.neon       # Production server
```

## Configuration (config/common.neon)

```neon
parameters:


application:
	mapping: App\Presentation\*\**Presenter


latte:
	strictParsing: yes
	extensions:
		- App\Presentation\Accessory\LatteExtension
```

## Services (config/services.neon)

```neon
services:
	- App\Core\RouterFactory::createRouter


search:
	-	in: %appDir%
		classes:
			- *Facade
			- *Factory
			- *Repository
			- *Service
```

## Router (app/Core/RouterFactory.php)

```php
<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}
}
```

## Presenter (app/Presentation/Home/HomePresenter.php)

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{
}
```

## Layout (app/Presentation/@layout.latte)

```latte
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">

	<title>{ifset title}{include title|stripHtml} | {/ifset}Nette Web</title>

	{asset? 'main.js'}
</head>

<body>
	<div n:foreach="$flashes as $flash" n:class="flash, $flash->type">{$flash->message}</div>

	{include content}
</body>
</html>
```

## Template (app/Presentation/Home/default.latte)

```latte
{block content}
<h1 n:block="title">Welcome</h1>

<p>Content here</p>
```

## Test Bootstrap (tests/bootstrap.php)

```php
<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Tester\Environment::setup();
Tester\Environment::setupFunctions();
```

## Presenter Mapping

The mapping `App\Presentation\*\**Presenter` means:
- `Home:default` → `App\Presentation\Home\HomePresenter::renderDefault()`
- `Admin:Dashboard:default` → `App\Presentation\Admin\Dashboard\DashboardPresenter::renderDefault()`
