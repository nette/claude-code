---
name: latte-templates
description: Invoke before creating or modifying .latte files, even for single-line changes. Provides Latte syntax, tags, filters, n:attributes, layouts, template inheritance, AJAX snippets, extensions, and Nette integration. Also trigger when user mentions Latte by name.
---

## Latte Templating System

Latte is a secure templating engine with context-aware escaping, intuitive syntax, and powerful template inheritance.

```shell
composer require latte/latte
```

### Basic Syntax

```latte
{* this is a comment *}
<ul n:if="$items">                {* n:attribute *}
{foreach $items as $item}         {* tag *}
	<li>{$item|capitalize}</li>   {* variable with filter *}
{/foreach}
</ul>
```

### Printing Variables

```latte
{$name}                    {* prints escaped variable *}
{$user->name}              {* object property *}
{$items[0]}                {* array access *}
{='hello'|upper}           {* expression with filter *}
{$html|noescape}           {* disable escaping (use carefully!) *}
```

Latte uses **context-aware escaping** — output adapts to where the variable is printed. In `<script>` context, arrays and objects are automatically serialized to JSON:

```latte
{* Arrays auto-serialize to JSON inside <script> *}
<script type="application/ld+json">{$schemaData}</script>

<script>
	let config = {$config};
</script>
```

Never use `json_encode()` in PHP + `|noescape` for this — Latte handles serialization and escaping safely on its own.

### Filters

Filters modify output, written after `|`:

```latte
{$title|upper}                    {* HELLO *}
{$text|truncate:100}              {* shortens to 100 chars *}
{$price|number:2}                 {* formats number *}
{$date|date:'j. n. Y'}            {* formats date *}
{$name|lower|capitalize}          {* chained filters *}
```

Common filters: `upper`, `lower`, `capitalize`, `truncate`, `number`, `date`, `noescape`, `escapeUrl`, `stripHtml`, `trim`, `replace`, `first`, `last`, `length`, `sort`, `reverse`

See [the complete filter reference](references/filters.md) for all available filters.

### n:attributes

Pair tags can be written as HTML attributes:

```latte
{* These are equivalent: *}
{if $condition}<div>...</div>{/if}
<div n:if="$condition">...</div>

{* Applies to element content only: *}
<div n:inner-foreach="$items as $item">...</div>

{* Applies to tag only (not content): *}
<a href={$url} n:tag-if="$url">Link</a>
```

### Conditions

```latte
{if $stock > 0}
	In stock
{elseif $onWay}
	On the way
{else}
	Not available
{/if}

{ifset $user}...{/ifset}          {* if variable exists *}

{* switch/case *}
{switch $type}
	{case admin}Administrator
	{case user}User
	{default}Guest
{/switch}
```

### Loops

```latte
{foreach $items as $item}
	{$item->name}
{/foreach}

{foreach $items as $key => $item}
	{$key}: {$item}
{/foreach}

{* With else for empty arrays *}
{foreach $items as $item}
	<li>{$item}</li>
{else}
	<li>No items found</li>
{/foreach}

{* Iterator variable *}
{foreach $items as $item}
	{$iterator->counter}. {$item}  {* 1, 2, 3... *}
	{if $iterator->first}First!{/if}
	{if $iterator->last}Last!{/if}
{/foreach}

{* Helper tags *}
{foreach $items as $item}
	{first}<ul>{/first}
	<li>{$item}</li>
	{last}</ul>{/last}
	{sep}, {/sep}                  {* separator between items *}
{/foreach}
```

### Variables

```latte
{var $name = 'John'}
{var $items = [1, 2, 3]}
{default $lang = 'en'}            {* only if not set *}

{capture $content}
	<p>Captured HTML</p>
{/capture}
{$content}
```

### Template Inheritance

**Layout template (`@layout.latte`):**

```latte
<!DOCTYPE html>
<html>
<head>
	<title>{block title}Default{/block}</title>
</head>
<body>
	{block content}{/block}
</body>
</html>
```

**Child template:**

```latte
{layout '@layout.latte'}

{block title}My Page{/block}

{block content}
	<h1>Welcome</h1>
	<p>Content here</p>
{/block}
```

### Including Templates

```latte
{include 'header.latte'}
{include 'item.latte', item: $item, showPrice: true}
{include $dynamicTemplate}
```

### Blocks

```latte
{block sidebar}
	<aside>Sidebar content</aside>
{/block}

{include sidebar}                          {* print block *}
{include sidebar from 'other.latte'}       {* from another file *}

{* Reusable definitions with parameters *}
{define button, $text, $type = 'primary'}
	<button class="btn btn-{$type}">{$text}</button>
{/define}

{include button, 'Submit'}
{include button, 'Cancel', 'secondary'}
```

### Common Tags Reference

| Tag | Description |
|-----|-------------|
| `{$var}` | Print escaped variable |
| `{if}...{/if}` | Condition |
| `{foreach}...{/foreach}` | Loop |
| `{var $x = ...}` | Create variable |
| `{include 'file'}` | Include template |
| `{block name}...{/block}` | Define block |
| `{layout 'file'}` | Extend layout |
| `{do expression}` | Execute without output |
| `{php expression}` | Execute PHP expression |
| `{dump $var}` | Debug dump (Tracy) |

See [the complete tag reference](references/tags.md) for all available tags.

### Smart HTML Attributes

```latte
{* null removes attribute *}
<div title={$title}>

{* boolean controls presence *}
<input type="checkbox" checked={$isChecked}>

{* arrays in class *}
<div class={['btn', active => $isActive]}>

{* arrays JSON-encoded in data- *}
<div data-config={[theme: dark, count: 5]}>
```

### n:class Helper

```latte
{foreach $items as $item}
	<a n:class="$item->active ? active, $iterator->first ? first, item">
		{$item->name}
	</a>
{/foreach}
```

---

## Latte in Nette Applications

### Template Organization Strategy

**Keep templates with presenters:**

```
Product/
├── ProductPresenter.php
├── default.latte
├── edit.latte
└── detail.latte
```

**Layout placement follows presenter organization:**

```
Admin/
├── @layout.latte           ← Admin-wide layout
├── Auth/
│   ├── @layout.latte       ← Auth-specific layout
│   └── AuthPresenter.php
└── Catalog/
	└── Product/
		├── ProductPresenter.php
		└── edit.latte
```

### Template Partial Patterns

**Shared template parts use @ prefix:**
- `@layout.latte` - layout templates
- `@form.latte` - reusable form structures
- `@item.latte` - list item templates

### Passing Variables to Templates

The standard way is assigning to `$this->template`:

```php
$this->template->article = $this->articles->getById($id);
```

For properties that should always be available in templates, use the `#[TemplateVariable]` attribute (requires public or protected visibility) instead of repeating assignments in every action:

```php
use Nette\Application\Attributes\TemplateVariable;

class ArticlePresenter extends Nette\Application\UI\Presenter
{
	#[TemplateVariable]
	public string $siteName = 'My blog';
}
```

The property value is automatically passed as `$siteName` in every template. If you explicitly assign `$this->template->siteName` in an action, the explicit value wins.

### Template Class Strategy

**Create template classes for complex presenters:**

```php
/**
 * @property-read ProductTemplate $template
 */
class ProductPresenter extends BasePresenter
{
}

class ProductTemplate extends Nette\Bridges\ApplicationLatte\Template
{
	public ProductRow $product;
	public array $variants;
	public ?CategoryRow $category;
}
```

**When to use template classes:**
- Presenters with 5+ template variables
- Complex data structures passed to templates
- When you want full IDE support in templates

**Template Type Declaration:**

```latte
{templateType App\Presentation\Product\ProductTemplate}

<h1>{$product->name}</h1>
{foreach $variants as $variant}
	<div class="variant">{$variant->name} - {$variant->price}</div>
{/foreach}
```

### Nette-specific Tags

```latte
{* Links *}
<a n:href="Product:detail $id">Detail</a>
<a href={link Product:detail $id}>Detail</a>
<a href={plink //Product:detail $id}>Absolute</a>

{* Components *}
{control productForm}
{control dataGrid}

{* AJAX Snippets *}
{snippet items}
	{foreach $items as $item}
		<div>{$item->name}</div>
	{/foreach}
{/snippet}

{* Forms *}
{form loginForm}
	{label username}{input username}
	{label password}{input password}
	{input submit}
{/form}

{* Assets (Nette Assets) *}
{asset 'admin.js'}
{asset 'front.css'}
```

### Extension and Customization

**Create single extension for entire application:**

```php
final class LatteExtension extends Latte\Extension
{
	public function getFilters(): array
	{
		return [
			'money' => fn($amount) => number_format($amount, 0, ',', ' ') . ' Kč',
		];
	}

	public function getFunctions(): array
	{
		return [
			'canEdit' => fn($entity) => $this->user->isAllowed($entity, 'edit'),
		];
	}
}
```

**Register in config:**

```neon
latte:
	strictParsing: yes
	extensions:
		- App\Presentation\Accessory\LatteExtension
```

Enable `strictParsing` to catch template errors early (missing variables, typos in tag names).

### Anti-Patterns to Avoid

- **Don't put business logic in templates** – templates display data, they don't process it. Calculations, filtering, and data transformations belong in presenters or services. Complex template logic is a sign that the presenter's `render*` method isn't preparing data well enough.
- **Don't create deep template hierarchies** – more than 2 levels of `{layout}` inheritance becomes hard to debug. Prefer `{include}` and `{define}` for composition over deep inheritance chains.
- **Don't duplicate template code** – if the same HTML structure appears in multiple templates, extract it to a `{define}` block or a partial template (`@item.latte`). Duplication causes inconsistency when one copy gets updated but not the others.

### Online Documentation

For detailed information, use WebFetch on these URLs:

- [Syntax](https://latte.nette.org/en/syntax) – complete syntax guide
- [Tags](https://latte.nette.org/en/tags) – all available tags
- [Filters](https://latte.nette.org/en/filters) – all filters with examples
- [Template Inheritance](https://latte.nette.org/en/template-inheritance) – layouts, blocks, embed
- [Extending Latte](https://latte.nette.org/en/extending-latte) – custom tags, filters, extensions
- [Type System](https://latte.nette.org/en/type-system) – template types and IDE support
