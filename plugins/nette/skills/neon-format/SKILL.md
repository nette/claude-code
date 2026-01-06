---
name: neon-format
description: Invoke before creating or modifying .neon files. Provides NEON syntax and Nette configuration conventions.
---

## NEON Format

NEON (Nette Object Notation) is a human-readable data format used for configuration files in Nette. Similar to YAML but with support for entities and tab indentation.

```shell
composer require nette/neon
```

### Mappings

Key-value pairs with required space after colon:

```neon
street: 742 Evergreen Terrace
city: Springfield
country: USA
```

Inline notation with braces:

```neon
{street: 742 Evergreen Terrace, city: Springfield, country: USA}
```

### Sequences

Indexed arrays with hyphen and space:

```neon
- Cat
- Dog
- Goldfish
```

Inline notation with brackets:

```neon
[Cat, Dog, Goldfish]
```

### Nesting

Indentation defines structure:

```neon
pets:
	- Cat
	- Dog
cars:
	- Volvo
	- Skoda
```

Block and inline can be combined:

```neon
pets: [Cat, Dog]
cars:
	- Volvo
	- Skoda
```

### Strings

Unquoted, single-quoted, or double-quoted:

```neon
- An unquoted string
- 'Single-quoted string'
- "Double-quoted with \t escapes"
```

Quote strings containing: `# " ' , : = - [ ] { } ( )`

Double a quote to include it: `'It''s working'`

Multiline strings with triple quotes:

```neon
'''
	first line
		second line
	third line
'''
```

### Special Values

```neon
# Numbers
count: 12
price: 12.3
scientific: +1.2e-34
binary: 0b11010
octal: 0o666
hex: 0x7A

# Null
value: null
empty:

# Booleans
enabled: true
disabled: false
active: yes
inactive: no

# Dates (auto-converted to DateTimeImmutable)
date: 2016-06-03
datetime: 2016-06-03 19:00:00
with_tz: 2016-06-03 19:00:00 +02:00
```

### Entities

Function-like structures for DI configuration:

```neon
Column(type: int, nulls: yes)
```

Chained entities:

```neon
Column(type: int) Field(id: 1)
```

Multiline entity:

```neon
Column(
	type: int
	nulls: yes
)
```

### Comments

```neon
# This line is ignored
street: 742 Evergreen Terrace  # inline comment
```

### Key Rules

- Space after `:` is required
- Use tabs for indentation
- Block notation cannot be nested inside inline notation
- Unquoted strings cannot start/end with spaces or look like numbers/booleans/dates

## PHP API

```php
use Nette\Neon\Neon;
```

### encode(mixed $value, bool $blockMode=false, string $indentation="\t"): string

Converts PHP value to NEON. Pass `true` to `$blockMode` for multiline output.

```php
Neon::encode($value);       // inline NEON
Neon::encode($value, true); // multiline NEON
```

### decode(string $neon): mixed

Parses NEON string to PHP value. Dates become `DateTimeImmutable`, entities become `Nette\Neon\Entity`.

```php
Neon::decode('hello: world'); // ['hello' => 'world']
```

### decodeFile(string $file): mixed

Parses NEON file to PHP value (removes BOM).

```php
Neon::decodeFile('config.neon');
```

All methods throw `Nette\Neon\Exception` on error.

### Lint Command

Check syntax errors in `.neon` files:

```shell
vendor/bin/neon-lint <path>
```
