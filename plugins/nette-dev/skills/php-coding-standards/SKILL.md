---
name: php-coding-standards
description: Invoke BEFORE writing or modifying any PHP code. Provides coding standards and naming conventions for Nette repos: TABs, single quotes, strict_types, PSR-12 modifications, use statement ordering. Use this whenever creating, modifying, or refactoring any PHP code - even small bug fixes or one-line changes.
---

## PHP Coding Standards

### Using nette/coding-standard

Install globally using `/php-fixer:install-php-fixer`. After installation, PHP files are automatically fixed when edited - see the `php-auto-fixer` skill for editing workflow rules (especially `use` statement ordering).

### General Rules
- Every PHP file must include `declare(strict_types=1)`
- Use two empty lines between methods - Nette convention for visual separation in longer classes
- Document shut-up operator use: `@mkdir($dir); // @ - directory may already exist`
- Document weak comparison operators: `// == accept null`
- Multiple exceptions can be written in a single `exceptions.php` file, and multiple enums into `enums.php`
- Interface methods don't need visibility as they're always public
- All properties, return values, and parameters must have types
- Final constants don't need types as they're self-evident
- Write all code, comments, and variables in English only (even if communicating with the user in Czech)

### Strings
- Use single quotes - they signal "no interpolation here," making code easier to scan
- Use double quotes only when the string contains apostrophes or interpolation is needed
- In HTML attributes, double quotes are standard

### Naming Conventions
- Avoid abbreviations unless the full name is too long
- Use UPPERCASE for two-letter abbreviations (`IO`, `DB`), PascalCase/camelCase for longer ones (`Http`, `Xml`)
- Use nouns or noun phrases for class names
- Class names should include both specificity and generality (e.g., `ArrayIterator`)
- PascalCase for classes and class constants/enums
- camelCase for methods and properties
- Never use prefixes/suffixes like `Abstract`, `Interface`, or `I` - the type system already distinguishes them

### Formatting
- Use TABs for indentation everywhere (PHP, JS, HTML, CSS/SCSS, NEON, Latte, ...)
- PHP follows Nette Coding Standard (based on PSR-12) with these modifications:
  - No space before parentheses in arrow functions: `fn($a) => $b`
  - No blank lines required between different `use` import types
  - When parameters span multiple lines, return type and opening brace go on separate lines:

```php
// Short params - standard single-line
public function getItems(string $type): array
{
	// method body
}

// Multi-line params - return type and brace on separate lines
public function example(
	string $param,
	array $options,
): string
{
	// method body
}
```

### Global Functions and Constants
- Write global functions/constants without leading backslash: `count($arr)` not `\count($arr)`
- For compiler-optimizable functions, add `use function` at the file beginning:
  ```php
  use Nette;
  use function count, is_array, is_scalar, sprintf;
  ```
- Occasionally import constants that may help the compiler:
  ```php
  use const PHP_OS_FAMILY;
  ```

### Code Style Preferences
- Uses DOM API with HTML5 parser Lexbor for HTML processing
- Use try/catch for external operations (file I/O, network, database)
- Prefer modern PHP syntax and concise expressions:
  - Example: `if (is_array($response['data'] ?? null))` instead of `if (isset($response['data']) && is_array($response['data']))`
- Use named arguments for boolean parameters whose meaning isn't obvious from context (e.g., `is_a($obj, $class, allow_string: true)`), but not when the method name makes it clear (e.g., `setReadonly(true)`)
- Place interface/base class outside the namespace containing its implementations (e.g., `Foo\Network` next to `Foo\Networks\*`, not inside it) - this keeps the interface discoverable at the package level
