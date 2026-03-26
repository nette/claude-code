---
name: php-doc
description: Invoke BEFORE writing phpDoc. Provides Nette conventions for docblocks: when to skip documentation, class/method/property/exception docs, generic array types (array<T>, list<T>), conditional return types. Use this whenever writing or editing any /** */ comment - even when the user just says "document this" without mentioning phpDoc.
---

## Documentation (phpDoc)

### Golden Rule

**Never duplicate signature information without adding value.** If the class name, method name, parameter names, and PHP types already tell the full story, skip the docblock entirely.

Skip documentation when:
- The method is trivial and self-explanatory (getters, setters, simple delegations)
- The docblock would only repeat what the signature already says
- Parameter names are descriptive enough (`$width`, `$height`, `$name`)

Write documentation when:
- The method has non-obvious behavior (returns null in specific conditions, throws exceptions)
- Array contents need specification (`@return string[]`)
- Parameters have constraints, formats, or valid ranges not captured by PHP types
- The "why" behind a design choice matters for callers

### Writing Style
- Be concise and direct - avoid unnecessary words
- Start method descriptions with 3rd person singular present tense verb: Returns, Formats, Checks, Creates, Converts, Sets
- Skip phrases like "Class that...", "Interface for...", "Method that..."
- Don't duplicate method lists or implementation details in class docblocks
- Use active phrasing describing main responsibility

### Property Documentation
Use single-line format for simple `@var` annotations:

```php
/** @var string[] */
private array $name;
```

Short comments for non-obvious properties:
```php
/** for back compatibility */
protected Explorer $context;
```

### Parameters and Return Values
- Prefer `?Type` over `Type|null` for nullable types
- Only document parameters when adding information beyond PHP types
- Use two-space alignment for readability in multi-param blocks:
  ```php
  /**
   * @return string  primary column sequence name
   * @param  mixed   $var  description here
   */
  ```

Include parameter docs only when explaining:
- Additional context or constraints
- Limitations or conditions
- Unusual usage patterns

### Generic Array Types

#### Array key types
- `array<T>` = `array<int|string, T>` - any keys (omitting key type means int|string)
- `array<int, T>` - int keys only (not necessarily sequential)
- `array<string, T>` - string keys only
- `list<T>` - sequential int keys starting from 0 (0, 1, 2...)

#### Input vs output distinction
- **@return**: `list<T>` is accurate if the function always returns sequential keys
- **@param**: `list<T>` can be too restrictive - may reject valid inputs with non-sequential keys

#### When to use `list<T>` for input
Analyze the implementation:
- `foreach ($arr as $v)` - doesn't use keys → `array<T>` is sufficient
- `$arr[0]`, `$arr[1]` - accesses by index → requires `list<T>`

#### Conditional return types
For return types dependent on parameters:
```php
/**
 * @return ($flag is true ? list<array{string, int}> : list<string>)
 */
```

#### How to determine the correct type
1. Examine the implementation - how is the array used?
2. Write a test script that outputs the result structure if needed
3. For nested arrays, analyze each nesting level separately

### Exception Documentation
- Use a single natural-language sentence describing the problem
- Avoid phrases like "Exception that is thrown when..."
- Use consistent phrases:
  - "does not exist" - for missing items
  - "failed to" - for operation failures
  - "cannot" - for impossible operations
  - "is not supported" - for unsupported features

Examples:
- "The file does not exist."
- "Cannot access the requested class property."
- "The value is outside the allowed range."
- "Failed to read from or write to a stream."

### Examples

#### Good Documentation

Clear purpose, adding array contents info:
```php
/**
 * Returns list of supported languages.
 * @return string[]  Array of language codes
 */
public function getSupportedLanguages(): array
```

Describing unusual behavior:
```php
/**
 * Creates new transaction. Returns null if user has exceeded daily limit.
 */
public function createTransaction(float $amount): ?Transaction
```

#### When to Skip

```php
// Signature says it all - no docblock needed
protected readonly string $name;

public function getWidth(): int

public function setName(string $name): void
```

Self-explanatory parameters - document only the method purpose:
```php
/**
 * Calculates dimensions of image cutout.
 */
public function calculateCutout(int $left, int $top, int $width, int $height): array
```
