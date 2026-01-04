---
name: latte-templates
description: Provides Latte templating syntax and conventions. Use when creating templates, working with Latte syntax, tags, filters, layouts, template classes, or Latte extensions.
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

For complete filter reference, see [filters.md](filters.md).

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
| `{php code}` | Raw PHP (needs extension) |
| `{dump $var}` | Debug dump (Tracy) |

For complete tag reference, see [tags.md](tags.md).

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
	extensions:
		- App\Presentation\Accessory\LatteExtension
```

### Latte Configuration

```neon
latte:
	strictParsing: yes
	locale: cs
```

### Anti-Patterns to Avoid

- **Don't put business logic in templates** - templates display data, don't process it
- **Don't create deep template hierarchies** - prefer composition over inheritance
- **Don't duplicate template code** - extract to partials or components

---

## Online Documentation

For detailed information beyond this reference, fetch from latte.nette.org:

- [Syntax](https://latte.nette.org/en/syntax) - complete syntax guide with examples
- [Tags](https://latte.nette.org/en/tags) - all available tags in detail
- [Filters](https://latte.nette.org/en/filters) - all filters with usage examples
- [Template Inheritance](https://latte.nette.org/en/template-inheritance) - layouts, blocks, embed
- [Functions](https://latte.nette.org/en/functions) - built-in functions
- [Extending Latte](https://latte.nette.org/en/extending-latte) - custom tags, filters, extensions
- [Type System](https://latte.nette.org/en/type-system) - template types and IDE support
- [Safety First](https://latte.nette.org/en/safety-first) - security and escaping

When you need more details about a specific Latte feature not covered in this skill, use WebFetch to retrieve information from these URLs.
