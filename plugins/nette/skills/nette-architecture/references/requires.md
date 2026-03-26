# #[Requires] Attribute

Declarative access control for presenter actions and signals. Instead of writing manual `if (!$this->isAjax())` checks in every action, the attribute enforces constraints at the framework level - the action method is never called if the condition isn't met.

When a requirement is violated, the framework throws `Nette\Application\BadRequestException`, so the user sees the error page.

## Available Parameters

| Parameter | Type | Effect |
|-----------|------|--------|
| `methods` | `string\|string[]` | Restrict allowed HTTP methods |
| `ajax` | `bool` | Require XMLHttpRequest |
| `forward` | `bool` | Require internal forward (block direct URL access) |
| `sameOrigin` | `bool` | Require same-origin request |

## Examples

```php
use Nette\Application\Attributes\Requires;

class AdminPresenter extends BasePresenter
{
	// State-changing signals should require POST + AJAX to prevent
	// accidental triggering via GET links or cross-site requests
	#[Requires(ajax: true, methods: 'POST')]
	public function handleDelete(int $id): void
	{
	}

	// Form processing - POST only
	#[Requires(methods: 'POST')]
	public function actionSave(): void
	{
	}

	// Allow both GET (display form) and POST (submit)
	#[Requires(methods: ['GET', 'POST'])]
	public function actionEdit(int $id): void
	{
	}

	// Confirmation step that should only be reachable via forward
	// from another action, not by typing the URL directly
	#[Requires(forward: true)]
	public function actionConfirm(): void
	{
	}
}
```

Apply to entire presenter - all actions and signals inherit the constraint:

```php
#[Requires(ajax: true)]
class ApiPresenter extends BasePresenter
{
	// All actions require AJAX
}
```
