---
name: nette-configuration
description: Invoke before configuring Nette DI - services, .neon files, autowiring.
---

## Nette DI & Services Guidelines

- **Primary services:** `services.neon` - all service definitions
- **Framework config:** `common.neon` - parameters, extensions, framework settings, application-wide configuration
- **Project scaling:** Create additional files for larger projects (`api.neon`, `tasks.neon`)
- **Environment-specific settings:** `env.local.neon` for development environment, `env.prod.neon` for production environment

### When to Add Sections

- **Start minimal** - Add only what you immediately need
- **Add incrementally** - Include sections as features are implemented
- **Environment-specific** - Override in local/production configs as needed

### Service Definition Syntax

Use `-` for services that don't need references:

```neon
services:
	- App\Model\BlogFacade
	- App\Model\CustomerService
	- App\Presentation\Accessory\TemplateFilters
```

Give names **only when needed for `@serviceName` references** elsewhere in NEON:

```neon
services:
	# Named because referenced as @pohoda
	pohoda:
		create: Nette\Database\Connection('odbc:Driver={...}')
		autowired: false

	# Using the reference
	- App\Model\PohodaImporter(pohoda: @pohoda)
```

Nette DI **automatically autowires all dependencies by type**. When manually specifying parameters, use **named parameters** if not the first parameter:

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
			mailbox: '{imap.gmail.com:993/ssl}'
			username: 'vi....com'
			password: 'nrllp...'
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

#### Disabled Autowiring

```neon
services:
	specialDb:
		create: Nette\Database\Connection(...)
		autowired: false  # Won't be injected automatically
```

#### Anti-Patterns to Avoid

**Don't name services unnecessarily** - only name when you need `@serviceName` references.
**Don't create services manually** - use DI container injection instead.
**Don't use positional parameters** - use named parameters when mixing with autowiring.
**Don't register every class** - only classes that need injection or are injected elsewhere.
**Don't hardcode secrets** - use parameters and environment variables.
**Don't bypass autowiring without reason** - let Nette DI handle dependencies automatically.

### Automatic Service Registration

Use `search` section for classes following naming conventions:

```neon
search:
	model:
		in: %appDir%
		classes:          # Classes end with `Factory`, `Facade`, or `Service`
			- *Factory
			- *Facade
			- *Service
```

### Parameter Usage

Reference configuration parameters with `%parameterName%`:

```neon
services:
	- App\Model\TexyProcessor('', %wwwDir%, %tempDir%/texy)
	- App\Tasks\ImportTask(path: %rootDir%/../data/import)
	- App\Model\CustomerService(ip: %ip%)
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

#### Authentication

Add when you need simple file-based authentication

```neon
security:
	users:                     # Simple username/password auth
		username: password
```
