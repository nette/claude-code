---
name: nette-forms
description: Invoke before creating or modifying Nette Forms. Provides form controls, validation, rendering patterns.
---

## Nette Forms

Nette Forms provides secure, reusable forms with automatic validation on both client and server side.

```shell
composer require nette/forms
```

### Forms in Presenters

Forms are created in factory methods named `createComponent<Name>`:

```php
protected function createComponentRegistrationForm(): Form
{
	$form = new Form;
	$form->addText('name', 'Name:')
		->setRequired('Please enter your name.');

	$form->addEmail('email', 'Email:')
		->setRequired('Please enter your email.');

	$form->addPassword('password', 'Password:')
		->setRequired('Please enter password.')
		->addRule($form::MinLength, 'Password must be at least %d characters', 8);

	$form->addSubmit('send', 'Register');

	$form->onSuccess[] = $this->registrationFormSucceeded(...);
	return $form;
}

private function registrationFormSucceeded(Form $form, \stdClass $data): void
{
	// $data->name, $data->email, $data->password
	$this->flashMessage('Registration successful.');
	$this->redirect('Home:');
}
```

Render in template:

```latte
{control registrationForm}
```

### Create and Edit Pattern

Unified form for both creating and editing records:

```php
class ProductPresenter extends BasePresenter
{
	public function __construct(
		private ProductFacade $facade,
	) {}

	public function renderEdit(int $id = null): void
	{
		$this->template->product = $id
			? $this->facade->getProduct($id)
			: null;
	}

	protected function createComponentProductForm(): Form
	{
		$form = new Form;
		$form->addText('name', 'Name:')
			->setRequired();
		$form->addTextArea('description', 'Description:');
		$form->addInteger('price', 'Price:')
			->setRequired()
			->addRule($form::Min, 'Price must be positive', 1);
		$form->addSubmit('send', 'Save');
		$form->onSuccess[] = $this->productFormSucceeded(...);
		return $form;
	}

	private function productFormSucceeded(Form $form, \stdClass $data): void
	{
		$id = $this->getParameter('id');
		if ($id) {
			$this->facade->update($id, (array) $data);
			$this->flashMessage('Product updated.');
		} else {
			$this->facade->create((array) $data);
			$this->flashMessage('Product created.');
		}
		$this->redirect('default');
	}
}
```

Template with edit check:

```latte
{block content}
<h1>{if $product}Edit: {$product->name}{else}New Product{/if}</h1>

{form productForm}
	{if $product}
		{$form['name']->setDefaultValue($product->name)}
		{$form['description']->setDefaultValue($product->description)}
		{$form['price']->setDefaultValue($product->price)}
	{/if}
	<table>
		<tr>
			<td>{label name}{input name}</td>
		</tr>
		<tr>
			<td>{label description}{input description}</td>
		</tr>
		<tr>
			<td>{label price}{input price}</td>
		</tr>
	</table>
	{input send}
{/form}
{/block}
```

### Form Reuse with Factory

For forms used in multiple places, create a factory:

```php
final class ProductFormFactory
{
	public function __construct(
		private FormFactory $formFactory,
	) {}

	public function create(callable $onSuccess): Form
	{
		$form = $this->formFactory->create();
		$form->addText('name', 'Name:')
			->setRequired();
		$form->addTextArea('description', 'Description:');
		$form->addInteger('price', 'Price:')
			->setRequired();
		$form->addSubmit('send');
		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess) {
			$onSuccess($data);
		};
		return $form;
	}
}
```

Use in presenter:

```php
public function __construct(
	private ProductFormFactory $productFormFactory,
) {}

protected function createComponentProductForm(): Form
{
	return $this->productFormFactory->create(
		function (\stdClass $data): void {
			$this->facade->save($data);
			$this->redirect('default');
		},
	);
}
```

### Common Form Controls

| Method | Creates |
|--------|---------|
| `addText($name, $label)` | Text input |
| `addPassword($name, $label)` | Password input |
| `addTextArea($name, $label)` | Multi-line text |
| `addEmail($name, $label)` | Email with validation |
| `addInteger($name, $label)` | Integer input |
| `addFloat($name, $label)` | Decimal input |
| `addCheckbox($name, $caption)` | Checkbox |
| `addCheckboxList($name, $label, $items)` | Multiple checkboxes |
| `addRadioList($name, $label, $items)` | Radio buttons |
| `addSelect($name, $label, $items)` | Dropdown |
| `addMultiSelect($name, $label, $items)` | Multi-select |
| `addUpload($name, $label)` | File upload |
| `addMultiUpload($name, $label)` | Multiple files |
| `addDate($name, $label)` | Date picker |
| `addHidden($name)` | Hidden field |
| `addSubmit($name, $caption)` | Submit button |
| `addButton($name, $caption)` | Button |

For complete control reference, see [controls.md](controls.md).

### Basic Validation

```php
$form->addText('name')
	->setRequired('Name is required.')
	->addRule($form::MinLength, 'At least %d characters', 3)
	->addRule($form::MaxLength, 'Maximum %d characters', 100);

$form->addEmail('email')
	->setRequired()
	->addRule($form::Email, 'Invalid email format.');

$form->addInteger('age')
	->addRule($form::Range, 'Age must be between %d and %d', [18, 120]);

$form->addPassword('password')
	->setRequired()
	->addRule($form::MinLength, 'At least %d characters', 8);

$form->addPassword('password2')
	->setRequired()
	->addRule($form::Equal, 'Passwords must match', $form['password']);
```

For complete validation reference, see [validation.md](validation.md).

### Conditional Validation

```php
$form->addCheckbox('newsletter', 'Subscribe to newsletter');

$form->addEmail('email')
	->addConditionOn($form['newsletter'], $form::Equal, true)
		->setRequired('Email required for newsletter.');
```

### Form Rendering

**Default rendering:**
```latte
{control productForm}
```

**Manual rendering:**
```latte
{form productForm}
<table>
	<tr>
		<th>{label name /}</th>
		<td>{input name} {inputError name}</td>
	</tr>
	<tr>
		<th>{label email /}</th>
		<td>{input email} {inputError email}</td>
	</tr>
</table>
{input send}
{/form}
```

**With Bootstrap:**
```latte
{form productForm class => 'form-horizontal'}
<div class="mb-3">
	{label name class => 'form-label' /}
	{input name class => 'form-control'}
	{inputError name class => 'invalid-feedback'}
</div>
{/form}
```

For complete rendering reference, see [rendering.md](rendering.md).

### Form Events

```php
// Before rendering (modify form)
$form->onRender[] = function (Form $form): void {
	// Add CSS classes, modify controls
};

// After successful validation
$form->onSuccess[] = function (Form $form, \stdClass $data): void {
	// Process valid data
};

// After any submission (valid or invalid)
$form->onSubmit[] = function (Form $form): void {
	// Logging, analytics
};

// Custom validation after rules pass
$form->onValidate[] = function (Form $form): void {
	if ($this->isBlocked($form->getValues()->email)) {
		$form->addError('This email is blocked.');
	}
};
```

### Error Handling

```php
private function productFormSucceeded(Form $form, \stdClass $data): void
{
	try {
		$this->facade->save($data);
		$this->redirect('default');
	} catch (DuplicateEntryException) {
		$form['email']->addError('Email already exists.');
	} catch (\Exception $e) {
		$form->addError('An error occurred.');
	}
}
```

### Anti-Patterns to Avoid

- **Don't put business logic in form handlers** - use services/facades
- **Don't create forms in action methods** - use createComponent* factory
- **Don't validate twice** - use form validation, not manual checking
- **Don't skip setRequired()** - always validate required fields

---

## Online Documentation

For detailed information, fetch from doc.nette.org:

- [Forms](https://doc.nette.org/en/forms) - complete forms guide
- [Controls](https://doc.nette.org/en/forms/controls) - all form controls
- [Validation](https://doc.nette.org/en/forms/validation) - validation rules
- [Rendering](https://doc.nette.org/en/forms/rendering) - rendering options
