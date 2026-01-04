# Latte Filters Reference

## Usage

```latte
{$var|filter}                     {* basic *}
{$var|filter:arg1:arg2}           {* with arguments *}
{$var|filter1|filter2}            {* chained *}
{$var?|filter}                    {* nullsafe - skips if null *}
{($expr)|filter}                  {* on expression *}
{block|filter}...{/block}         {* on block *}
```

## String Transformation

| Filter | Description | Example |
|--------|-------------|---------|
| `truncate:length` | Shorten preserving words | `{$text\|truncate:100}` |
| `substr:start:length` | Extract substring | `{$s\|substr:0:10}` |
| `trim` | Strip whitespace | `{$s\|trim}` |
| `strip` / `spaceless` | Remove extra whitespace | `{$html\|spaceless}` |
| `stripHtml` | Remove HTML tags | `{$html\|stripHtml}` |
| `indent:level` | Indent text | `{$code\|indent:2}` |
| `replace:search:replace` | Replace string | `{$s\|replace:'foo':'bar'}` |
| `replaceRE:pattern:replace` | Regex replace | `{$s\|replaceRE:'/\\d+/':'X'}` |
| `padLeft:length:char` | Pad from left | `{$n\|padLeft:5:'0'}` |
| `padRight:length:char` | Pad from right | `{$s\|padRight:20}` |
| `repeat:times` | Repeat string | `{$s\|repeat:3}` |
| `reverse` | Reverse string/array | `{$s\|reverse}` |
| `webalize` | URL-safe slug | `{$title\|webalize}` |

## Letter Case

| Filter | Description | Example |
|--------|-------------|---------|
| `upper` | UPPERCASE | `{$s\|upper}` |
| `lower` | lowercase | `{$s\|lower}` |
| `capitalize` | Each Word Capitalized | `{$s\|capitalize}` |
| `firstUpper` | First letter upper | `{$s\|firstUpper}` |
| `firstLower` | First letter lower | `{$s\|firstLower}` |

## Numbers

| Filter | Description | Example |
|--------|-------------|---------|
| `number:decimals` | Format number | `{$n\|number:2}` → `1,234.56` |
| `number:format` | ICU format | `{$n\|number:'#,##0.00'}` |
| `round:precision` | Round | `{$n\|round:1}` |
| `floor:precision` | Round down | `{$n\|floor}` |
| `ceil:precision` | Round up | `{$n\|ceil}` |
| `clamp:min:max` | Clamp to range | `{$n\|clamp:0:100}` |
| `bytes` | Format file size | `{$size\|bytes}` → `1.5 MB` |

## Date & Time

| Filter | Description | Example |
|--------|-------------|---------|
| `date:format` | Format date (PHP) | `{$d\|date:'j. n. Y'}` |
| `localDate:format` | Locale-aware date | `{$d\|localDate: date: long}` |

```latte
{$date|date:'j. n. Y'}           {* 15. 4. 2024 *}
{$date|date:'H:i'}               {* 14:30 *}
{$date|localDate: date: short}   {* locale-dependent *}
```

## Arrays

| Filter | Description | Example |
|--------|-------------|---------|
| `first` | First element | `{$arr\|first}` |
| `last` | Last element | `{$arr\|last}` |
| `random` | Random element | `{$arr\|random}` |
| `length` | Count elements | `{$arr\|length}` |
| `slice:start:length` | Extract slice | `{$arr\|slice:0:5}` |
| `sort` | Sort array | `{$arr\|sort}` |
| `sort:by:key` | Sort by key | `{$arr\|sort: by: name}` |
| `reverse` | Reverse order | `{$arr\|reverse}` |
| `column:key` | Extract column | `{$arr\|column:'name'}` |
| `group:by` | Group by key | `{$arr\|group:'category'}` |
| `batch:size` | Split into chunks | `{$arr\|batch:3}` |
| `implode:glue` / `join` | Join to string | `{$arr\|implode:', '}` |
| `commas` | Join with commas | `{$arr\|commas}` |
| `commas:lastGlue` | Join with last separator | `{$arr\|commas:' and '}` |
| `explode:sep` / `split` | Split to array | `{$s\|explode:','}` |

```latte
{foreach ($items|sort: by: price) as $item}...{/foreach}

{$tags|implode:', '}             {* foo, bar, baz *}
{$items|commas: ' and '}         {* a, b and c *}
```

## Escaping

Latte automatically escapes all output with **context-sensitive escaping** - the only PHP templating language with this feature. It detects whether output is in HTML, JavaScript, CSS, or URL context and applies the correct escaping function automatically. You never need to write `|escapeHtml`, `|escapeJs`, `|escapeCss`, or `|escapeXml`.

| Filter | Description |
|--------|-------------|
| `escapeUrl` | Escape URL parameter (for query string values) |
| `noescape` | Disable automatic escaping |
| `query` | Generate query string from array |

```latte
<a href="?q={$search|escapeUrl}">

{* Query from array *}
<a href="?{[name: 'John', age: 30]|query}">
{* outputs: ?name=John&age=30 *}
```

## Security

Latte automatically adds `|checkUrl` to all `href` and `src` attributes. This filter validates that URLs lead to web pages (http, https, or relative paths) and blocks dangerous protocols like `javascript:` or `data:`. You rarely need to write `|checkUrl` manually.

| Filter | Description |
|--------|-------------|
| `nocheck` | Disable automatic URL checking |

## HTML

| Filter | Description | Example |
|--------|-------------|---------|
| `breakLines` | `\n` → `<br>` | `{$text\|breakLines}` |
| `dataStream` | Convert to data URI | `{$image\|dataStream}` |

## Translation

| Filter | Description |
|--------|-------------|
| `translate` | Translate string |

```latte
{'Hello'|translate}
```

## Registering Custom Filters

```php
// In Latte extension
public function getFilters(): array
{
	return [
		'money' => fn($amount) => number_format($amount, 0, ',', ' ') . ' Kč',
		'ago' => fn($date) => $this->formatTimeAgo($date),
	];
}
```

```latte
{$price|money}    {* 1 234 Kč *}
{$date|ago}       {* 2 hours ago *}
```
