---
name: nette-architecture
description: Provides Nette application architecture guidelines. Use when working with presenters, modules, directory structure, Core vs Model decisions, or Nette MVC patterns.
---

For new project skeleton, see [skeleton.md](skeleton.md).

## Backend Architecture

- The project requires PHP 8.3+, Nette 3.2, Latte 3.1

### Key Framework Features

- Presenters: Controllers that handle HTTP requests in Nette
- Latte: Templating engine used for views
- Dependency Injection: Service management through Nette's DI container
- Router: URL routing through `RouterFactory`

### Directory Structure

The application follows domain-driven organization with preference for flatter structure:

- App: The main application namespace (`App\`)
  - Bootstrap: Application initialization and configuration
  - Core: Infrastructure concerns (routing, etc.)
  - Entity: All database entities in single namespace
  - Model: Business logic services organized by domain
  - Presentation: UI layer organized by modules
  - Tasks: Command-line executable tasks

### Evolution Strategy

**Start minimal** → **Grow organically** → **Refactor when painful**

Start with flat structure - create subdirectories only when you have 5+ related files or clear implementation variants.
Don't architect for theoretical future complexity. Address actual complexity when it emerges with clear user needs driving structural decisions.

### Configuration

- config/common.neon: Main application configuration
- config/services.neon: Service definitions and auto-wiring configuration

### Core vs Model Decision Matrix

**Use Core/ for:**
- Technology-agnostic infrastructure (MyExplorer, RouterFactory, QueueMailer)
- External service integrations (SentryLogger, AI/, GoogleSearch/)
- Framework extensions and utilities
- Code that could be moved to another project unchanged

**Use Model/ for:**
- Business domain logic (CatalogService, CustomerService, OrderService)
- Domain-specific operations and rules
- Entity-specific processing logic
- Code that knows about your business concepts (products, orders, customers)


**Why flat:** Entities often cross domain boundaries. ProductRow might be used in catalog, orders, and inventory contexts.

### Model Layer Principles

```
app/Model/
├── CatalogService.php      ← Main domain services at root
├── CustomerService.php
├── OrderService.php
├── mails/                  ← Email templates (specialized assets)
├── Payment/                ← Implementation variants
│   ├── CardOnlinePayment.php
│   ├── BankTransferPayment.php
│   └── CashPayment.php
└── exceptions.php          ← Domain exceptions
```

**Service placement rules:**
1. Main domain coordinator services directly in Model/
2. Implementation variants get subdirectories when 3+ implementations exist
3. Specialized assets (templates, exceptions) in focused locations


### Module Structure

```
app/Presentation/
├── Accessory/              ← UI shared across entire application
│   ├── LatteExtension.php
│   └── TemplateFilters.php
├── Admin/
│   ├── BasePresenter.php  ← Admin-specific functionality
│   ├── Auth/              ← Authentication
│   ├── Catalog/           ← Product management
│   │   ├── Brand/
│   │   ├── List/          ← Overview/utility presenters
│   │   └── Product/
│   └── Fulfill/           ← Order processing
└── Front/
	├── Customer/
	└── Listing/
```

**Keep presenters flat until complexity demands structure:**

```
# Start simple
Dashboard/DashboardPresenter.php

# Grow when needed
Admin/Catalog/Product/ProductPresenter.php
Admin/Catalog/Brand/BrandPresenter.php
Admin/Catalog/List/ListPresenter.php
```

**Create nested structure when:**
- Single functional area has 4+ presenters
- Clear sub-domains emerge (Product management, Order fulfillment)
- Shared logic between related presenters


### Base Presenter Strategy

Create BasePresenter for each major module **only when needed:**
- `Admin\BasePresenter` - authentication checks, admin-specific setup
- Contains common `beforeRender()`, authentication logic, shared template variables

**Avoid deep inheritance** - prefer composition over inheritance chains deeper than BasePresenter → SpecificPresenter.

### Accessory Placement Decision Tree

**Use Presentation/Accessory/ for:**
- Components used across multiple modules (navigation, forms)
- Latte extensions and template filters
- Shared template functionality

**Use Module/Accessory/ for:**
- Components specific to that module but used by multiple presenters
- Module-specific template helpers

**Use Presenter directory for:**
- Components used only by that presenter
- Presenter-specific forms and controls

### When to Create New Module

**Create module when:**
- You have 5+ related presenters
- Functionality has distinct user base (Admin vs Front vs Api)
- Different authentication/authorization requirements
- Separate URL structure patterns

**Avoid modules for:**
- Single presenter with single purpose
- Artificial separation without clear user/functional boundaries


### Tasks and Command Organization

```
app/Tasks/
├── Maintenance/          ← Cleanup, optimization
├── Integration/          ← External data sync
└── Scheduled/            ← Recurring operations
```

**Task responsibility boundaries:**
- Tasks handle execution context (CLI arguments, error handling, scheduling)
- Business logic stays in Model services
- Tasks coordinate, services execute

### Anti-Patterns to Avoid

**Don't create directories prematurely** - wait until you have actual complexity, not anticipated complexity.

**Don't separate by technical layer** - avoid Services/, Repositories/, Controllers/ separation in favor of domain organization.

**Don't create deep hierarchies** - prefer descriptive names over nested structure (OrderFulfillmentService vs Fulfill/Order/Service).

**Don't duplicate Base presenter logic** - use inheritance or traits instead of copying common functionality.

### #[Requires] Attribute

Control access to presenter actions and signals:

```php
use Nette\Application\Attributes\Requires;

class AdminPresenter extends BasePresenter
{
	// Require AJAX only
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
	}

	// Require POST method
	#[Requires(methods: 'POST')]
	public function actionSave(): void
	{
	}

	// Require specific methods
	#[Requires(methods: ['GET', 'POST'])]
	public function actionEdit(int $id): void
	{
	}

	// Require forward (not direct access)
	#[Requires(forward: true)]
	public function actionConfirm(): void
	{
	}

	// Combine requirements
	#[Requires(ajax: true, methods: 'POST')]
	public function handleUpdate(): void
	{
	}
}
```

Apply to entire presenter:

```php
#[Requires(ajax: true)]
class ApiPresenter extends BasePresenter
{
	// All actions require AJAX
}
```

### Passing Settings to Presenters

**Via DI container (recommended):**

```neon
# config/services.neon
services:
	- App\Presentation\Admin\ProductPresenter(itemsPerPage: 20)
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

**Via parameters:**

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
	- App\Presentation\ProductPresenter(
		itemsPerPage: %pagination.itemsPerPage%
	)
```

**Via base presenter:**

```php
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public int $itemsPerPage = 20;

	public function injectSettings(Settings $settings): void
	{
		$this->itemsPerPage = $settings->get('pagination.itemsPerPage', 20);
	}
}
