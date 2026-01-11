---
name: php-coding-standards
description: Invoke BEFORE writing PHP code. Provides coding standards and naming conventions.
---

## PHP Coding Standards

### Using nette/coding-standard

Install globally using the `/install-nette-cs` command.

After installation, PHP files are automatically checked when edited. Do not run `ecs` manually.

### General Rules
- Every PHP file must include `declare(strict_types=1)`
- Use two empty lines between methods for better readability
- Document shut-up operator use: `@mkdir($dir); // @ - directory may already exist`
- Document weak comparison operators: `// == accept null`
- Multiple exceptions can be written in a single `exceptions.php` file
- Interface methods don't need visibility as they're always public
- All properties, return values, and parameters must have types
- Final constants don't need types as they're self-evident
- Use single quotes for strings unless containing apostrophes
- Write all code, comments, variables etc. in English only! (even if you communicate with me in chat in Czech)

### Naming Conventions
- Avoid abbreviations unless full name is too long
- Use UPPERCASE for two-letter abbreviations, PascalCase/camelCase for longer ones
- Use nouns or noun phrases for class names
- Class names should include both specificity and generality (e.g., `ArrayIterator`)
- Use PascalCaps for class constants and enums
- Never use prefixes/suffixes like `Abstract`, `Interface`, or `I` for interfaces/abstract classes
- PascalCase for classes, camelCase for methods/properties

### Formatting
- Use TABS for indentation (Everywhere! V PHP, JS, HTML, CSS/SCSS, NEON, ...)
- Prefer to use single quotes (except for HTML)
- PHP follows Nette Coding Standard (based on PSR-12) with these modifications:
  - No space before parentheses in arrow functions: `fn($a) => $b`
  - No blank lines required between different `use` import types
  - Return type and opening brace on separate lines:

```php
public function example(
	string $param,
	array $options,
):
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

### Other rules
- Use try/catch for external operations
- Prefer modern PHP syntax and concise expressions
  - Example: Use `if (is_array($response['data'] ?? null))` instead of `if (isset($response['data']) && is_array($response['data']))`
- Uses DOM API with HTML5 parser Lexbor
- Use named arguments for boolean parameters whose meaning isn't obvious from context (e.g., `is_a($obj, $class, allow_string: true)`), but not when the method name makes it clear (e.g., `setReadonly(true)`)
- Place interface/base class outside the namespace containing its implementations (e.g., `Foo\Network` next to `Foo\Networks\*`, not inside it)
