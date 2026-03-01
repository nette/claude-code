# Nette Forms Rendering Reference

## Default Rendering

Render entire form with default renderer:

```latte
{control registrationForm}
```

## Manual Rendering

### Form Tags

```latte
{form signInForm}
	... form content ...
{/form}
```

With attributes:

```latte
{form signInForm class => 'ajax', id => 'frm-signin'}
```

### Labels and Inputs

```latte
{* Paired tags *}
{label username}Username:{/label}
{input username}

{* Self-closing label *}
{label username /}

{* With extra attributes *}
{input username class => 'form-control', autofocus}

{* Error message *}
{inputError username}
```

### Form Buttons

```latte
{input send}
{input send class => 'btn btn-primary'}
```

### Container Content

```latte
{* Render container controls *}
{formContainer address}
	{input street}
	{input city}
	{input zip}
{/formContainer}
```

## Complete Manual Example

```latte
{form signInForm}
<table>
	<tr class="required">
		<th>{label username /}</th>
		<td>{input username} {inputError username}</td>
	</tr>
	<tr class="required">
		<th>{label password /}</th>
		<td>{input password} {inputError password}</td>
	</tr>
	<tr>
		<th></th>
		<td>{input remember} {label remember /}</td>
	</tr>
	<tr>
		<th></th>
		<td>{input send}</td>
	</tr>
</table>
{/form}
```

## Accessing Form Object

```latte
{var $form = $form}
<p>Form errors: {$form->getErrors()|implode:', '}</p>

{foreach $form->getControls() as $control}
	{if $control instanceof Nette\Forms\Controls\TextInput}
		<div>{$control->getControl()}</div>
	{/if}
{/foreach}
```

## Bootstrap 5 Integration

### Horizontal Form

```latte
{form signInForm class => 'row g-3'}
<div class="col-md-6">
	{label username class => 'form-label' /}
	{input username class => 'form-control'}
	{inputError username class => 'invalid-feedback d-block'}
</div>
<div class="col-md-6">
	{label password class => 'form-label' /}
	{input password class => 'form-control'}
	{inputError password class => 'invalid-feedback d-block'}
</div>
<div class="col-12">
	{input send class => 'btn btn-primary'}
</div>
{/form}
```

### Floating Labels

```latte
{form signInForm}
<div class="form-floating mb-3">
	{input email class => 'form-control', placeholder => 'name@example.com'}
	{label email class => 'form-label'}Email{/label}
</div>
<div class="form-floating mb-3">
	{input password class => 'form-control', placeholder => 'Password'}
	{label password class => 'form-label'}Password{/label}
</div>
{/form}
```

## Customizing DefaultRenderer

```php
$renderer = $form->getRenderer();
$renderer->wrappers['controls']['container'] = 'div class="form-group"';
$renderer->wrappers['pair']['container'] = 'div class="row mb-3"';
$renderer->wrappers['label']['container'] = 'div class="col-sm-3"';
$renderer->wrappers['control']['container'] = 'div class="col-sm-9"';
```

## Renderer Wrappers

| Wrapper | Description |
|---------|-------------|
| `form.container` | Form element wrapper |
| `form.errors` | Form errors container |
| `group.container` | Control group wrapper |
| `group.label` | Group label |
| `controls.container` | All controls wrapper |
| `pair.container` | Label + control pair |
| `control.container` | Single control wrapper |
| `control.description` | Control description |
| `control.errors` | Control errors |
| `label.container` | Label wrapper |
| `label.suffix` | After label text |
| `label.requiredsuffix` | Required field indicator |

Example:

```php
$renderer = $form->getRenderer();
$renderer->wrappers['label']['requiredsuffix'] = ' *';
$renderer->wrappers['control']['errorcontainer'] = 'span class="error"';
```

## Custom Renderer

Implement `Nette\Forms\FormRenderer`:

```php
class MyRenderer implements Nette\Forms\FormRenderer
{
	public function render(Form $form): string
	{
		$html = '<form ' . $form->getElementPrototype()->attributes() . '>';
		foreach ($form->getControls() as $control) {
			$html .= $this->renderControl($control);
		}
		$html .= '</form>';
		return $html;
	}

	private function renderControl($control): string
	{
		// Custom rendering logic
	}
}

$form->setRenderer(new MyRenderer);
```

## n:name Attribute

Activate form controls in HTML elements:

```latte
<form n:name="signInForm">
	<input n:name="username">
	<button n:name="send">Sign In</button>
</form>
```

Extended:

```latte
<select n:name="country">
	<option value="">Choose...</option>
	{foreach $countries as $code => $name}
		<option value={$code}>{$name}</option>
	{/foreach}
</select>
```

Checkbox:

```latte
<input n:name="agree" type="checkbox">
<label n:name="agree">I agree to terms</label>
```

## Translation

Enable translation for labels and messages:

```php
$form->setTranslator($translator);
```

Or translate in template:

```latte
{label username}{_'Username'}{/label}
```

## AJAX Forms

Add ajax class for Naja:

```latte
{form searchForm class => ajax}
	{input query}
	{input send}
{/form}
```

Handle in presenter:

```php
public function handleSearch(): void
{
	$form = $this['searchForm'];
	if ($form->isSubmitted() && $form->isValid()) {
		$this->template->results = $this->search($form->getValues()->query);
		$this->redrawControl('results');
	}
}
```
