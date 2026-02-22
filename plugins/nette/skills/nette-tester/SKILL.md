---
name: nette-tester
description: Read this SKILL before running tests, evaluating test results, or writing/modifying any .phpt test files. Provides Nette Tester conventions, Assert methods, and tester commands.
---

## Testing with Nette Tester

We use Nette Tester for unit testing. Test files should have `.phpt` extension.

```shell
composer require nette/tester --dev
```

### Bootstrap File

The bootstrap file should set up the Tester environment and enable helper functions:

```php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();  // enables test(), testException(), testNoError(), setUp()
```

### Basic Test Structure

```php
<?php declare(strict_types=1);

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
- Test file naming: `{ClassName}.phpt` or `{ClassName}.{feature}.phpt`

### Assertions Overview

- `Assert::same($expected, $actual)` — strict identity (`===`)
- `Assert::notSame($expected, $actual)` — not strictly equal
- `Assert::equal($expected, $actual)` — loose comparison (ignores object identity, array key order, float epsilon)
- `Assert::notEqual($expected, $actual)`
- `Assert::true($actual)`, `Assert::false($actual)`, `Assert::null($actual)`, `Assert::notNull($actual)`
- `Assert::truthy($actual)`, `Assert::falsey($actual)`
- `Assert::contains($needle, $haystack)` — checks substring or array element; **avoid for testing output** (see warning below)
- `Assert::notContains($needle, $haystack)`
- `Assert::hasKey($key, $array)`, `Assert::hasNotKey($key, $array)`
- `Assert::count($count, $value)`
- `Assert::type($type, $value)` — class/interface or built-in type (`'string'`, `'int'`, `'list'`, etc.)
- `Assert::match($pattern, $actual)` — pattern matching with placeholders (see below)
- `Assert::matchFile($file, $actual)` — pattern loaded from file
- `Assert::exception($fn, $class, $message, $code)` — asserts exception is thrown
- `Assert::error($fn, $type, $message)` — asserts PHP error/warning/deprecation is generated
- `Assert::noError($fn)` — asserts no errors or exceptions

**Warning about Assert::contains:** Do not use `Assert::contains()` for testing generated output (HTML, text, etc.). It only checks for a substring — the test will pass even if the output contains errors or is completely broken, as long as the needle appears somewhere. Use `Assert::match()` or `Assert::matchFile()` instead, which verify the entire structure of the output.

### Pattern Matching with Assert::match

`Assert::match($pattern, $actual)` compares a string against a pattern with placeholders. `Assert::matchFile($file, $actual)` works the same way but loads the pattern from a file.

Available placeholders:

| Pattern | Meaning |
|---------|---------|
| `%a%` | one or more of anything except end of line |
| `%a?%` | zero or more of anything except end of line |
| `%A%` | one or more of anything including end of line |
| `%A?%` | zero or more of anything including end of line |
| `%s%` / `%s?%` | one or more / zero or more whitespace (except EOL) |
| `%S%` / `%S?%` | one or more / zero or more non-whitespace |
| `%c%` | a single character (except end of line) |
| `%d%` / `%d?%` | one or more / zero or more digits |
| `%i%` | signed integer |
| `%f%` | floating point number |
| `%h%` | one or more HEX digits |
| `%w%` | one or more alphanumeric characters |
| `%ds%` | directory separator (`/` or `\`) |
| `%%` | literal `%` character |

Important behavior:
- **Ungreedy matching** — placeholders match as little text as possible, so `%a%` captures the shortest possible string
- **Line endings are normalized** — `\r\n` and `\n` are treated as equivalent, so tests work cross-platform
- **Trailing whitespace is ignored**
- Patterns can also be raw regexps delimited by `~` or `#`

```php
Assert::match('<div class="item">%a%</div>', $html);

// For larger patterns, use NOWDOC syntax
Assert::match(<<<'XX'
	<html>
	<body>%A%</body>
	</html>
	XX, $html);

// Or load the pattern from a file (supports the same placeholders)
Assert::matchFile(__DIR__ . '/expected/output.html', $actual);
```

When `Assert::matchFile()` fails, the expected and actual output are written to the test output directory as `.expected` and `.actual` files.

### Testing Exceptions

For simple single-call exceptions, use the concise `fn()` style:

```php
Assert::exception(
	fn() => Arrays::pick($arr, 'undefined'),
	Nette\InvalidArgumentException::class,
	"Missing item '%s%'.",
);
```

The `Assert::exception()` method:
1. First parameter: A closure that should throw the exception
2. Second parameter: Expected exception class
3. Third parameter (optional): Expected exception message, can contain match placeholders (`%a%`, `%s%`, etc.)

For testing PHP errors and deprecations:

```php
Assert::error(
	fn() => $object->deprecatedMethod(),
	E_USER_DEPRECATED,
	'This method is deprecated',
);
```

If the entire `test()` block is to end with an exception, you can use `testException()`:

```php
testException('throws exception for invalid input', function () {
	$mapper = new FilesystemMapper(__DIR__ . '/fixtures');
	$mapper->getAsset('missing.txt');
}, AssetNotFoundException::class, "Asset file 'missing.txt' not found at path: %a%");
```

### Essential Commands

```bash
# Run tests in specific directory
vendor/bin/tester tests/filters/ -s

# Run with specific PHP binary
vendor/bin/tester tests/ -p php-cgi

# Run specific test file - each file is a fully functional executable test
php tests/common/Engine.phpt
```

### Test Output Directory

When a test fails, Nette Tester writes the expected and actual output into an `output` directory next to the test files (e.g. `tests/Tracy/output/`). For each failing test `Foo.phpt`, you will find:

- `Foo.expected` — what the test expected to see
- `Foo.actual` — what was actually produced

**Always look at these files first** when investigating test failures. Comparing `.expected` vs `.actual` shows the exact difference and is much more informative than the short failure message printed by the runner.
