---
name: phpstan-analysis
description: Invoke BEFORE running PHPStan or fixing PHPStan errors. Covers error resolution strategy (refactoring > phpDoc > ignoring), common Nette error patterns, baseline management, and type tests. Use this whenever the user mentions PHPStan, static analysis, type errors, or wants to suppress warnings - even for a single error.
---

## PHPStan Analysis

### Running PHPStan

```bash
# Run with project configuration
vendor/bin/phpstan analyse

# Run on specific paths
vendor/bin/phpstan analyse src/foo/ src/bar.php

# Generate baseline for legacy projects
vendor/bin/phpstan analyse --generate-baseline
```

Never use `--error-format=json` - its output format can change between PHPStan versions and is not designed for stable machine consumption. For machine-readable output, use `--error-format=raw`.

### Target Levels

Target level for Nette libraries is **8**. Levels higher than 8 are not worth pursuing - the additional strictness (e.g., `non-empty-string`, `positive-int`) catches very few real bugs relative to the annotation burden.

- **Level 7**: Union types checked
- **Level 8**: Null checks, strict types (our target)

### nette/phpstan-rules

All Nette libraries use `nette/phpstan-rules`. It provides:
- **Precise return types** for `Strings::match()`, `matchAll()`, `split()`, `Arrays::invoke()`, etc.
- **Removes `|false` and `|null`** from PHP functions where error values are unrealistic (`getcwd`, `json_encode`, `preg_split`, etc.)
- **Assert type narrowing** after `Tester\Assert` calls (`notNull()`, `type()`, `true()`)
- **False positive suppression** for Nette patterns (arrow functions as void callbacks, runtime validation closures)

---

## Error Resolution Strategy

### Create a Plan First

Before making any changes, create a plan:

1. **Group errors by type** (`property.nonObject`, `method.notFound`, `new.static`, etc.)
2. **For each type, choose resolution** (in order of preference):
   - **Refactoring** - does the error reveal a design issue?
   - **Better type annotations** - code is correct but types need clarification via phpDoc
   - **Ignoring** - false positive or not worth fixing (last resort)
3. **Justify each decision** with clear reasoning
4. **Present the plan** before implementing

### General Principles

1. **Prefer refactoring** - if an error reveals a design weakness, fix the design first
2. **Then consider phpDoc** - if the code is correct but types are imprecise
3. **Suppress only as last resort** - only for false positives or intentional design patterns
4. **Never silence errors** - a fix must not hide potential problems (see "Never Silence Errors" section below for examples)
5. **Never use `@phpstan-ignore` annotations** - keep checker-specific directives out of source code
6. Use `assert()` sparingly, only when type information cannot be expressed otherwise
7. Systematic patterns: ignore in `phpstan.neon` with a comment explaining why
8. Use baseline as last resort, minimize

### Refactoring as First Choice

Always ask: **does this error reveal a real design issue?** Examples:

- **Overly broad return types** - method returns `mixed` or `object` but always returns a specific type; narrow the return type
- **Interface too loose** - code calls a method on implementation but not on interface; extend the interface
- **Mixed responsibilities** - class handles too many types; split it
- **Unnecessary dynamic access** - `__get`/`__set` where typed properties would work

The goal is not to "make PHPStan happy" but to use its feedback as a catalyst for better code.

---

## Code Fixes Guidelines

### Never Silence Errors

The code worked before. A fix that hides an error degrades code quality.

```php
// Before - json_encode returns false on error and we find out (type error)
function foo(): string {
	return json_encode($this->value);
}

// WRONG - error is hidden
function foo(): string {
	return (string) json_encode($this->value);
}
```

Better solutions: use `Json::encode()`, add explicit check with exception, or ignore in baseline.

### Throw Expression Pattern

```php
// Before - fopen can return false
$f = fopen($file, 'r');

// Correct fix
$f = fopen($file, 'r') ?: throw new IOException("Cannot open file $file");
```

### Beware of `/** @var */` in Method Bodies

`/** @var Type */` in method body is taken authoritatively by PHPStan - it completely disables type checking for that variable. Use only when no better solution exists.

---

## phpDoc and Type Annotations

For phpDoc conventions (when to skip docs, array types, writing style), see the **php-doc** skill.

Key rules specific to PHPStan compatibility:

- phpDoc type must always match the native type:

| Native Type | Wrong phpDoc | Correct phpDoc |
|-------------|--------------|----------------|
| `array\|string` | `mixed[]` | `mixed[]\|string` |
| `array\|null` | `int[]` | `int[]\|null` |
| `object\|array` | `stdClass` | `stdClass\|array` |

- Don't use overly granular types (`positive-int`, `non-empty-string`, `non-empty-array`, `non-falsy-string`) - they rarely catch real bugs but add significant annotation maintenance burden
- Use `class-string<T>`, `array<string, Foo>`, `list<int>`, `array{name: string, age: int}` - these are useful for PHPStan and worth maintaining

**Array notation preference:**
- `foo[]` - always prefer for simple types (shortest notation)
- `array<foo|bar>` - for union types (more readable than `(foo|bar)[]`)
- `array<string, foo>` or `list<foo>` - when keys are not generic

---

## Common Nette Error Patterns

### `property.nonObject` - property access on `array|object`

In DI Extensions, `$this->config` returns `array|object` but is `stdClass`. This is one of the legitimate cases for `/** @var */` in method body - the type cannot be expressed otherwise:

```php
public function loadConfiguration(): void
{
	/** @var \stdClass $config */
	$config = $this->config;
}
```

### `method.notFound` / `staticMethod.notFound` / `arguments.count`

Calling method on interface that exists only on implementation. Fix type if possible, or use `assert()`:

```php
$component = $container->getComponent($name);
assert($component instanceof Component);
$component->saveState($params);
```

### `property.uninitializedReadonly` / `property.readOnlyAssignNotInConstructor`

Readonly properties initialized via inject methods (Nette DI pattern). Ignore - this is an intentional framework pattern.

### `new.static` - unsafe usage of `new static()`

If intentional design pattern (derive/factory methods), ignore in phpstan.neon. Or change to `new self` if subclassing isn't expected.

### `closure.unusedUse`

Variable in `use ($var)` used in `require`'d file. Ignore - false positive.

### `function.alreadyNarrowedType`

PHPStan knows the type is already narrowed. Remove unnecessary condition, or ignore if it serves as runtime validation.

### `catch.neverThrown`

Verify if the catch is actually needed. If so, ignore.

---

## Ignoring Errors

### Priority

1. **Fix** - always prefer
2. **phpstan.neon** - for systematic/intentional patterns, always with a comment
3. **phpstan-baseline.neon** - last resort, minimize

### Systematic Patterns in phpstan.neon

```neon
parameters:
	ignoreErrors:
		-   # Intentional design pattern for derive() and factory methods
			identifier: new.static
			paths:
				- src/Caching/Cache.php
				- src/Bridges/CacheLatte/Nodes/CacheNode.php

		-   # Runtime validation for untyped array input
			identifier: function.alreadyNarrowedType
			path: src/Caching/Cache.php
```

Always include a comment explaining why the error is ignored.

### Baseline

```bash
vendor/bin/phpstan analyse --generate-baseline
```

Use only for false positives that are not systematic, or individual cases where fix requires BC break.

---

## phpstan.neon Structure

```neon
parameters:
	level: 8

	paths:
		- src

	excludePaths:
		- src/compatibility.php
		# other files for historical compatibility

	ignoreErrors:
		# systematic patterns with comments

includes:
	- phpstan-baseline.neon
```

Exclude files for backward compatibility with historical versions (compatibility.php, Latte 2 support, etc.).

---

## Type Tests

Files in `tests/types/*.php` verify that types in the library are defined correctly.

**Purpose:**
- Guarantee that the library won't cause type problems for users
- Protect against unintended type changes during refactoring
- Especially important for complex generics

These tests must always pass and must never be ignored.

**Using TypeAssert (from nette/phpstan-rules):**

```php
use Nette\PHPStan\Tester\TypeAssert;

TypeAssert::assertTypes(__DIR__ . '/data/types.php');
TypeAssert::assertNoErrors(__DIR__ . '/data/clean.php');
```

**Data file with assertType:**

```php
use function PHPStan\Testing\assertType;

assertType('non-empty-string', getcwd());
assertType('string', Normalizer::normalize('foo'));
```

---

## Workflow

1. **Run PHPStan** and get list of errors
2. **Understand the project** - relationships between classes are essential
3. **Exclude** files for historical compatibility
4. **Create a plan** grouping errors by type with justification for each strategy
5. **Refactor code** where error reveals a design improvement
6. **Fix phpDoc** where code is correct but types are imprecise
7. **Add assert()** where necessary to communicate type to PHPStan
8. **Ignore in phpstan.neon** systematic patterns with a comment
9. **Generate baseline** for the rest (minimize)
10. **Verify** that tests pass
