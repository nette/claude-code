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

## Hidden Internal Views

Combine `Requires(forward: true)` with `switch()` (or `setView()`) to expose an alternate render that can only be reached through the presenter's own logic - never via URL. Useful for "not found" / "deleted" / "alphabet listing" branches that share the action's URL but render a different template.

```php
class ProductPresenter extends BasePresenter
{
	public function actionDefault(string $slug): void
	{
		$this->product = $this->catalog->getProductBySlug($slug) ?? $this->error();
		if (!$this->product->active) {
			$this->switch('deleted'); // jumps to renderDeleted()
		}
	}

	public function renderDefault(): void { /* normal product page */ }

	#[Requires(forward: true)]
	public function renderDeleted(): void
	{
		// reachable only via the switch() above, not via ?action=deleted
	}
}
```

The URL stays `Product:default` for both branches, so canonical links and SEO are preserved. The `Requires(forward: true)` guard prevents anyone from opening `renderDeleted()` directly.

### switch() vs setView()

Both set `$this->forwarded = true`, so both unlock `Requires(forward: true)`. The behavior depends on which phase you call them from:

| | called from `action*()` | called from `render*()` |
|---|---|---|
| `setView('foo')` | `renderFoo()` runs (no `actionFoo()`) | only the `.latte` file changes at `sendTemplate()` time; current `render*()` finishes |
| `switch('foo')` | `actionFoo()` runs from the start, then `renderFoo()` | `renderFoo()` runs from the start |

Practical guidance:

- Use **`setView()`** when you only want to redirect to a different template/render method, keeping the action's preparation work intact. It cannot restart the action method.
- Use **`switch()`** when the branch needs its own action method (clean state, different parameters), or when you need to switch from inside a render method to a different `render*()` with different data preparation. This is the only option in render phase if `actionFoo()` should also run, or if you need a fresh `render*()` invocation.

The presenters in `Listing/Brand` and `Listing/Category` call `switch('alphabet')` from `renderDefault()` because the alphabet listing has its own data preparation in `renderAlphabet()`. `ProductPresenter` calls `switch('deleted')` from `actionDefault()` because the action work (loading the product) has already happened and only the render branch differs.
