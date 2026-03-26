---
name: nette-configuration
description: Invoke before configuring Nette DI – services, .neon files, autowiring. Provides service registration, autowiring, auto-discovery, factory and setup methods, parameter usage, decorator section, and framework configuration. Also trigger for resolving autowiring issues or organizing config files.
---

## Nette DI & Services Guidelines

- **Primary services:** `services.neon` – all service definitions
- **Framework config:** `common.neon` – parameters, extensions, framework settings, includes
- **Project scaling:** Create additional files for larger projects (`api.neon`, `tasks.neon`)
- **Environment-specific settings:** `env.local.neon` for development, `env.prod.neon` for production

### Config File Includes

`common.neon` includes other files. Later files override earlier ones, so environment-specific files go last:

```neon
includes:
	- services.neon
	- env.local.neon   # Gitignored, overrides for local dev
```

### Extensions

Register DI extensions for third-party packages or custom container builders:

```neon
extensions:
	console: Contributte\Console\DI\ConsoleExtension
	translation: Contributte\Translation\DI\TranslationExtension
```

### Service Definition Syntax

Use `-` for services that don't need references. Nette DI automatically resolves all constructor dependencies by type, so most services need just their class name:

```neon
services:
	- App\Model\BlogFacade
	- App\Model\CustomerService
	- App\Presentation\Accessory\TemplateFilters
```

Give names **only when needed for `@serviceName` references** elsewhere in NEON. Unnecessary names add clutter and create a maintenance burden when classes are renamed:

```neon
services:
	# Named because referenced as @pohoda
	pohoda:
		create: Nette\Database\Connection('odbc:Driver={...}')
		autowired: false

	# Using the reference
	- App\Model\PohodaImporter(pohoda: @pohoda)
```

When manually specifying parameters, use **named parameters** if not the first parameter:

```neon
services:
	# Good - first parameter, clear order
	- App\Model\ImageService(%rootDir%/storage)

	# Good - named parameter when mixing with autowiring
	- App\Model\CustomerService(ip: %ip%)
	- App\Model\BlogFacade(blogPath: %blog%)
```

#### Factory Method Services

```neon
services:
	- App\Core\RouterFactory::createRouter
	- Symfony\Component\HttpClient\HttpClient::create()
```

#### Complex Service Configuration

```neon
services:
	- App\Model\MailImporter(
		DG\Imap\Mailbox(
			mailbox: %imap.mailbox%
			username: %imap.username%
			password: %imap.password%
		)
		debugMode: %debugMode%
	)
```

#### Setup Methods

```neon
services:
	database:
		create: PDO(%dsn%, %user%, %password%)
		setup:
			- setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)
```

#### Overriding Services by Type

When you need to alter an existing service (e.g. replace a framework service with your own implementation), you can refer to it by type using `@ClassName:` instead of guessing internal service names. The `alteration: true` is implicit:

```neon
services:
	# Override the framework's Application with a custom one
	@Nette\Application\Application:
		create: MyApplication
```

#### Disabled Autowiring

Use `autowired: false` when you have multiple services of the same type and need to prevent ambiguity (e.g. two database connections):

```neon
services:
	specialDb:
		create: Nette\Database\Connection(...)
		autowired: false  # Must reference explicitly via @specialDb
```

### Automatic Service Registration

Use `search` section to avoid manually registering services that follow naming conventions. This keeps services.neon short and ensures new services are picked up automatically:

```neon
search:
	model:
		in: %appDir%
		classes:          # Classes ending with Factory, Facade, or Service
			- *Factory
			- *Facade
			- *Service
```

### Decorator Section

Apply configuration to all services of a specific type without listing them individually. Useful for injecting common dependencies or calling setup methods on multiple services:

```neon
decorator:
	App\Presentation\BasePresenter:
		setup:
			- setTranslator
```

### Parameter Usage

Reference configuration parameters with `%parameterName%`. Parameters are defined in the `parameters` section of common.neon and can be overridden per environment:

```neon
services:
	- App\Model\TexyProcessor('', %wwwDir%, %tempDir%/texy)
	- App\Tasks\ImportTask(path: %rootDir%/../data/import)
	- App\Model\CustomerService(ip: %ip%)
```

### Passing Settings to Services and Presenters

**Via service arguments (recommended)** – explicit, type-safe, visible in constructor:

```neon
# config/services.neon
services:
	- App\Presentation\Admin\Product\ProductPresenter(itemsPerPage: 20)
```

```php
class ProductPresenter extends BasePresenter
{
	public function __construct(
		private int $itemsPerPage,
		private ProductFacade $facade,
	) {}
}
```

**Via parameters** – useful when the same value is used in multiple places:

```neon
# config/common.neon
parameters:
	pagination:
		itemsPerPage: 20
		maxItems: 1000
```

```neon
# config/services.neon
services:
	- App\Presentation\Admin\Product\ProductPresenter(
		itemsPerPage: %pagination.itemsPerPage%
	)
```

### Core Framework Configuration

Add sections to `common.neon` **only when you need specific functionality**.

#### Application Layer

**Add when:** Setting up error handling or custom URL mapping

```neon
application:
	mapping: App\Presentation\*\**Presenter
	errorPresenter:
		4xx: Error:Error4xx     # When you have custom error pages
		5xx: Error:Error5xx
	aliases:                    # When you have frequently used links in n:href or $presenter->link()
		home: 'Front:Home:'
		admin: 'Admin:Dashboard:'
```

#### HTTP and Session

**Add when:** You need custom cookie settings, proxy support, or session configuration

```neon
http:
	proxy: 10.0.0.0/8      # When behind a reverse proxy

session:
	expiration: 14 days
	cookieSamesite: Lax
```

#### Authentication

**Add when:** You need simple file-based authentication for development or prototyping. For production, implement a custom authenticator class instead.

```neon
security:
	users:                     # Development only - NOT for production
		username: password
```

### Anti-Patterns to Avoid

**Don't name services unnecessarily** – named services create coupling; when you rename the class, you must also update every `@name` reference. Only name when you need `@serviceName` references.

**Don't register every class** – only register classes that need injection or are injected elsewhere. Presenters and components are registered automatically.

**Don't hardcode secrets** – use parameters and load them from environment-specific config files (env.local.neon) that are gitignored.

**Don't use positional parameters** – use named parameters when mixing with autowiring. Positional arguments break when the constructor signature changes.

**Don't bypass autowiring without reason** – Nette DI handles dependencies automatically. Manual wiring is only needed for ambiguous types (multiple implementations of the same interface).

### Online Documentation

For detailed information, use WebFetch on these URLs:

- [DI Services](https://doc.nette.org/en/dependency-injection/services) – service registration and autowiring
- [DI Configuration](https://doc.nette.org/en/dependency-injection/configuration) – config file format
- [Application Configuration](https://doc.nette.org/en/application/configuration) – application, http, session settings
