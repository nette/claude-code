---
name: php-doc
description: Provides phpDoc documentation standards. Use when writing class, method, or property documentation in PHP.
---

## Documentation (phpDoc)

- **GOLDEN RULE:** Never duplicate signature information without adding value
- For trivial methods where class, method name, and parameter names explain everything
- When documentation would only repeat signature information
- Clearly define purpose/responsibility
- Use active phrasing describing main responsibility
- Avoid unnecessary words
- Be concise and direct

### Class Documentation

#### Writing Style
- Skip phrases like "Class that..." or "Interface for..."
- Don't duplicate method lists or implementation details

### Property Documentation
- Use single-line format for simple `@var` annotations:

```php
/** @var string[] */
private array $name;
```

### Method Documentation

#### Writing Style
- Start with 3rd person singular present tense verb
- Prefer verbs like Returns, Formats, Checks, Creates, Converts, Sets

#### Parameters and Return Values
- Only document when adding information beyond PHP types
- Add information about:
  - Array contents (e.g., `@return string[]`)
  - Specific formats or value ranges
  - Important verbal descriptions
- Use two spaces after type: `@return string  primary column sequence name`
- Use two spaces after param: `@param  mixed  $var`
- Skip for self-explanatory parameters (width, height, name)
- Include only when explaining:
  - Additional context
  - Limitations or conditions
  - Unusual usage

### Exception Documentation
- Avoid phrases like "Exception that is thrown when"
- Use natural language and active voice
- Ideally use a single sentence
- Describe the problem, not technical context
- Use consistent phrases across exceptions:
  - "does not exist" for missing items
  - "failed to" for operation failures
  - "cannot" for impossible operations
  - "is not supported" for unsupported features
- Avoid technical jargon, redundant words, implementation details

Examples:
- "The file does not exist."
- "Cannot access the requested class property."
- "The value is outside the allowed range."
- "Failed to read from or write to a stream."


### Examples

#### Good Documentation

Clear purpose, adding information about array contents:
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

Short for properties:
```php
/** for back compatibility */
protected Explorer $context;
```

#### When to Skip Documentation

When signature provides all needed information and purpose obvious:

```php
protected readonly string $name;

public function getWidth(): int
```

For self-explanatory parameters:

```php
/**
 * Calculates dimensions of image cutout.
 */
public function calculateCutout(int $left, int $top, int $width, int $height): array
```
