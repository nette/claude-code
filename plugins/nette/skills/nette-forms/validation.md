# Nette Forms Validation Reference

## Required Fields

```php
$form->addText('name', 'Name:')
	->setRequired('Please fill in your name.');
```

## Validation Rules

Add rules with `addRule()`:

```php
$form->addPassword('password', 'Password:')
	->addRule($form::MinLength, 'Password must be at least %d characters', 8);
```

**Rules are checked only if the user fills in the control.**

## Universal Rules

| Constant | Description | Argument |
|----------|-------------|----------|
| `Required` | Required field | - |
| `Filled` | Same as Required | - |
| `Blank` | Must be empty | - |
| `Equal` | Value equals parameter | `mixed` |
| `NotEqual` | Value differs from parameter | `mixed` |
| `IsIn` | Value is in array | `array` |
| `IsNotIn` | Value is not in array | `array` |
| `Valid` | Control passes validation | - |

## Text Input Rules

| Constant | Description | Argument |
|----------|-------------|----------|
| `MinLength` | Minimum text length | `int` |
| `MaxLength` | Maximum text length | `int` |
| `Length` | Length in range or exact | `[int, int]` or `int` |
| `Email` | Valid email | - |
| `URL` | Valid URL | - |
| `Pattern` | Matches regex | `string` |
| `PatternInsensitive` | Case-insensitive regex | `string` |
| `Integer` | Integer value | - |
| `Numeric` | Alias for Integer | - |
| `Float` | Decimal number | - |
| `Min` | Minimum value | `int\|float` |
| `Max` | Maximum value | `int\|float` |
| `Range` | Value in range | `[min, max]` |

## File Upload Rules

| Constant | Description | Argument |
|----------|-------------|----------|
| `MaxFileSize` | Maximum size in bytes | `int` |
| `MimeType` | MIME type (wildcards allowed) | `string\|array` |
| `Image` | JPEG, PNG, GIF, WebP, AVIF | - |
| `Pattern` | Filename matches regex | `string` |

## Multi-Select Rules

For `addMultiSelect()`, `addCheckboxList()`, `addMultiUpload()`:

| Constant | Description | Argument |
|----------|-------------|----------|
| `MinLength` | Minimum count | `int` |
| `MaxLength` | Maximum count | `int` |
| `Length` | Count in range | `[int, int]` or `int` |

## Examples

```php
// Text validation
$form->addText('username')
	->setRequired('Username is required.')
	->addRule($form::MinLength, 'At least %d characters', 3)
	->addRule($form::MaxLength, 'Maximum %d characters', 20)
	->addRule($form::Pattern, 'Only letters and numbers', '[a-zA-Z0-9]+');

// Email
$form->addEmail('email')
	->setRequired()
	->addRule($form::Email);

// Number range
$form->addInteger('age')
	->addRule($form::Range, 'Age must be %d-%d', [18, 120]);

// File upload
$form->addUpload('photo')
	->addRule($form::Image, 'Must be an image.')
	->addRule($form::MaxFileSize, 'Max %d bytes', 2 * 1024 * 1024);

// Password confirmation
$form->addPassword('password')
	->setRequired()
	->addRule($form::MinLength, 'At least %d characters', 8);

$form->addPassword('password2')
	->setRequired('Confirm password.')
	->addRule($form::Equal, 'Passwords must match', $form['password']);
```

## Conditions

Add rules conditionally with `addCondition()`:

```php
$form->addPassword('password')
	->addCondition($form::MaxLength, 8)    // if password <= 8 chars
		->addRule($form::Pattern, 'Must contain digit', '.*[0-9].*');
```

Condition on another control:

```php
$form->addCheckbox('newsletter', 'Subscribe');

$form->addEmail('email')
	->addConditionOn($form['newsletter'], $form::Equal, true)
		->setRequired('Email required for newsletter.');
```

Complex conditions:

```php
$form->addText('phone')
	->addCondition($form::Filled)
		->addConditionOn($form['country'], $form::Equal, 'cz')
			->addRule($form::Pattern, 'Czech format required', '\+420[0-9]{9}');
```

## Custom Rules

```php
class MyValidators
{
	public static function validateDivisibility(BaseControl $input, int $arg): bool
	{
		return $input->getValue() % $arg === 0;
	}
}

$form->addInteger('number')
	->addRule(
		[MyValidators::class, 'validateDivisibility'],
		'Value must be divisible by %d',
		5,
	);
```

## onValidate Event

Additional validation after rules pass:

```php
$form->onValidate[] = function (Form $form): void
{
	$data = $form->getValues();
	if ($data->password === $data->username) {
		$form->addError('Password cannot be same as username.');
	}
};
```

## Error Messages

Placeholders in messages:

| Placeholder | Replaced with |
|-------------|---------------|
| `%d` | Rule arguments in order |
| `%n$d` | N-th rule argument |
| `%label` | Control label |
| `%name` | Control name |
| `%value` | Entered value |

```php
$form->addText('name')
	->setRequired('Please fill in %label');

$form->addInteger('id')
	->addRule($form::Range, 'Must be between %d and %d', [5, 10]);
```

## Processing Errors

Add errors during form processing:

```php
private function formSucceeded(Form $form, \stdClass $data): void
{
	try {
		$this->userManager->register($data);
	} catch (DuplicateEmailException) {
		$form['email']->addError('Email already registered.');
		return;
	}
}
```

## Input Value Filtering

Modify values before validation:

```php
$form->addText('zip', 'ZIP:')
	->addFilter(fn($value) => str_replace(' ', '', $value))
	->addRule($form::Pattern, 'Invalid ZIP', '\d{5}');
```

## Disabling Validation

Skip validation for specific buttons:

```php
$form->addSubmit('cancel', 'Cancel')
	->setValidationScope([]);  // No validation

$form->addSubmit('preview', 'Preview')
	->setValidationScope([$form['name']]);  // Only validate name
```

## JavaScript Validation

Include netteForms.js for client-side validation:

```latte
<script src="https://unpkg.com/nette-forms@3"></script>
```

Or with npm:

```js
import netteForms from 'nette-forms';
netteForms.initOnLoad();
```

### Dynamic Visibility (toggle)

```php
$form->addCheckbox('sendMail')
	->addCondition($form::Equal, true)
		->toggle('#email-container');
```

```latte
<div id="email-container">
	{label email}{input email}
</div>
```
