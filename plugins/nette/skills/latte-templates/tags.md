# Latte Tags Reference

## Printing

| Tag | Description |
|-----|-------------|
| `{$var}`, `{=expr}` | Print escaped variable or expression |
| `{$var\|filter}` | Print with filters applied |
| `{l}`, `{r}` | Print literal `{` or `}` |

## Conditions

| Tag | Description |
|-----|-------------|
| `{if}...{elseif}...{else}...{/if}` | Condition |
| `{ifset $var}...{/ifset}` | If variable exists and is not null |
| `{ifchanged}...{/ifchanged}` | Test if value changed in loop |
| `{switch} {case} {default} {/switch}` | Switch statement |
| `n:if`, `n:else`, `n:elseif` | Condition as n:attribute |

```latte
{if $count > 0}
	{$count} items
{elseif $count === 0}
	No items
{else}
	Unknown
{/if}

<p n:if="$show">Visible</p>
<p n:else>Hidden</p>
```

## Loops

| Tag | Description |
|-----|-------------|
| `{foreach $arr as $item}...{/foreach}` | Foreach loop |
| `{for $i = 0; $i < 10; $i++}...{/for}` | For loop |
| `{while $cond}...{/while}` | While loop |
| `{continueIf $cond}` | Continue to next iteration |
| `{breakIf $cond}` | Break loop |
| `{skipIf $cond}` | Skip iteration (doesn't increment counter) |
| `{first}...{/first}` | Content for first iteration |
| `{last}...{/last}` | Content for last iteration |
| `{sep}...{/sep}` | Separator between items |

### $iterator Variable

Inside `{foreach}`:
- `$iterator->first` - is first iteration?
- `$iterator->last` - is last iteration?
- `$iterator->counter` - iteration count (1, 2, 3...)
- `$iterator->counter0` - zero-based count (0, 1, 2...)
- `$iterator->odd`, `$iterator->even` - odd/even iteration?
- `$iterator->parent` - parent iterator in nested loops

```latte
{foreach $items as $item}
	<tr class={$iterator->odd ? 'odd' : 'even'}>
		<td>{$iterator->counter}.</td>
		<td>{$item->name}</td>
	</tr>
{/foreach}
```

## Template Inclusion

| Tag | Description |
|-----|-------------|
| `{include 'file.latte'}` | Include template |
| `{include 'file.latte', var: value}` | Include with variables |
| `{include block}` | Render block |
| `{include block from 'file.latte'}` | Render block from file |
| `{sandbox 'file.latte'}` | Include in sandbox mode |

## Blocks & Layouts

| Tag | Description |
|-----|-------------|
| `{block name}...{/block}` | Define named block |
| `{define name, $param}...{/define}` | Define reusable block with parameters |
| `{layout 'file.latte'}` | Extend layout template |
| `{import 'file.latte'}` | Import blocks from file |
| `{embed 'file.latte'}...{/embed}` | Embed template and override blocks |
| `{include parent}` | Include parent block content |

```latte
{* Layout *}
{block content}{/block}

{* Child *}
{layout '@layout.latte'}
{block content}
	<h1>Hello</h1>
	{include parent}  {* includes parent's content block *}
{/block}
```

## Variables

| Tag | Description |
|-----|-------------|
| `{var $x = value}` | Create variable |
| `{default $x = value}` | Create if doesn't exist |
| `{parameters $a, int $b = 0}` | Declare template parameters |
| `{capture $var}...{/capture}` | Capture output to variable |

## Types

| Tag | Description |
|-----|-------------|
| `{varType Type $var}` | Declare variable type |
| `{templateType ClassName}` | Declare template class type |
| `{varPrint}` | Print suggested variable types |
| `{templatePrint}` | Print suggested template class |

## Exception Handling

| Tag | Description |
|-----|-------------|
| `{try}...{else}...{/try}` | Catch exceptions |
| `{rollback}` | Discard try block |

```latte
{try}
	{include 'risky.latte'}
{else}
	<p>Failed to load</p>
{/try}
```

## Translation

| Tag | Description |
|-----|-------------|
| `{_'text'}`, `{_$var}` | Translate |
| `{translate}...{/translate}` | Translate block |

## HTML Helpers

| Tag | Description |
|-----|-------------|
| `n:class` | Dynamic class attribute |
| `n:attr` | Dynamic attributes |
| `n:tag` | Dynamic tag name |
| `n:ifcontent` | Omit empty element |

```latte
<div n:class="$active ? active, item">

<input n:attr="disabled: $disabled, readonly: $readonly">

<h{$level} n:tag="$level ? 'h' . $level : null">Title</h{$level}>

<div n:ifcontent>{$content}</div>  {* omits div if empty *}
```

## Other

| Tag | Description |
|-----|-------------|
| `{do $expr}` | Execute without output |
| `{dump $var}` | Dump variable (Tracy) |
| `{debugbreak}` | Debugger breakpoint |
| `{spaceless}...{/spaceless}` | Remove whitespace |
| `{syntax off}...{/syntax}` | Disable Latte parsing |
| `{contentType text/xml}` | Set content type |
| `{trace}` | Show template stack trace |

## Nette Framework Only

| Tag | Description |
|-----|-------------|
| `n:href` | Link in `<a>` element |
| `{link Presenter:action}` | Generate link |
| `{plink Presenter:action}` | Persistent link |
| `{control name}` | Render component |
| `{snippet name}...{/snippet}` | AJAX snippet |
| `{snippetArea name}` | Snippet wrapper |
| `{cache}...{/cache}` | Cache output |

## Nette Forms

| Tag | Description |
|-----|-------------|
| `{form name}...{/form}` | Form tags |
| `{label name}`, `{input name}` | Form controls |
| `{inputError name}` | Form error message |
| `n:name` | Activate form control |
| `{formContainer name}...{/formContainer}` | Form container |

## Nette Assets

| Tag | Description |
|-----|-------------|
| `{asset 'file.js'}` | Render asset |
| `{preload 'file.js'}` | Preload hint |
| `n:asset` | Asset attributes |
