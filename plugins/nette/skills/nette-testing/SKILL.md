---
name: nette-testing
description: Invoke before writing or modifying any .phpt test files. Provides Nette Tester conventions, Assert methods, and tester commands.
---

## Testing with Nette Tester

We use Nette Tester for unit testing. Test files should have `.phpt` extension.

```shell
composer require nette/tester --dev
```

### Bootstrap File

The bootstrap file should set up the Tester environment and enable helper functions:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();  // enables test(), testException(), testNoError()
```

### Basic Test Structure

```php
<?php

declare(strict_types=1);

use Tester\Assert;
use Nette\Assets\SomeClass;

require __DIR__ . '/../bootstrap.php';


test('SomeClass correctly does something', function () {
	$object = new SomeClass();
	$result = $object->doSomething();

	Assert::same('expected value', $result);
});


test('SomeClass handles edge case properly', function () {
	$object = new SomeClass();
	$result = $object->handleEdgeCase();

	Assert::true($result);
});
```

Key points:
- Use the `test()` function for each test case
- The first parameter of `test()` should be a clear description of what is being tested
- Do not add comments before `test()` calls - the description parameter serves this purpose
- Group related tests in the same file

### Testing Exceptions

To test if code correctly throws exceptions:

```php
Assert::exception(
	fn() => $mapper->getAsset('missing.txt'),
	AssetNotFoundException::class,
	"Asset file 'missing.txt' not found at path: %a%",
);
```

The `Assert::exception()` method:
1. First parameter: A closure that should throw the exception
2. Second parameter: Expected exception class
3. Third parameter (optional): Expected exception message, can contain placeholders (%a% means any text)

If the entire `test()` block is to end with an exception, you can use `testException()`:

```php
testException('throws exception for invalid input', function () {
	$mapper = new FilesystemMapper(__DIR__ . '/fixtures');<br>
	$mapper->getAsset('missing.txt');
}, AssetNotFoundException::class, "Asset file 'missing.txt' not found at path: %a%");
```

### Essential Commands

```bash
# Run all tests
composer tester

# Run specific test file
vendor/bin/tester tests/common/Engine.phpt -s

# Run tests in specific directory
vendor/bin/tester tests/filters/ -s
```

Static Analysis & Code Quality using PHPStan

```bash
# Run PHPStan static analysis
composer phpstan
```
