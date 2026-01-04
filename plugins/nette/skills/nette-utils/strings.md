# Strings

Static class `Nette\Utils\Strings` for working with UTF-8 strings.

```php
use Nette\Utils\Strings;
```

---

## Letter Case

Requires PHP `mbstring` extension.

```php
Strings::lower('Hello World');      // 'hello world'
Strings::upper('Hello World');      // 'HELLO WORLD'
Strings::firstUpper('hello world'); // 'Hello world'
Strings::firstLower('Hello World'); // 'hello world'
Strings::capitalize('hello world'); // 'Hello World'
```

---

## String Editing

### normalize(string $s): string

Removes control characters, normalizes line endings to `\n`, trims blank lines and trailing spaces, normalizes UTF-8 to NFC.

### unixNewLines / platformNewLines

```php
Strings::unixNewLines($string);     // converts to \n
Strings::platformNewLines($string); // \r\n on Windows, \n elsewhere
```

### webalize(string $s, ?string $charlist=null, bool $lower=true): string

Creates URL-safe slug. Removes diacritics, replaces non-alphanumeric with hyphens.

```php
Strings::webalize('žluťoučký kůň');         // 'zlutoucky-kun'
Strings::webalize('10. image_id', '._');    // '10.-image_id'
Strings::webalize('Hello', null, false);    // 'Hello' (keep case)
```

Requires PHP `intl` extension.

### trim(string $s, ?string $charlist=null): string

UTF-8 aware trim.

```php
Strings::trim('  Hello  '); // 'Hello'
```

### truncate(string $s, int $maxLen, string $append='…'): string

Truncates preserving whole words.

```php
$text = 'Hello, how are you today?';
Strings::truncate($text, 5);       // 'Hell…'
Strings::truncate($text, 20);      // 'Hello, how are you…'
Strings::truncate($text, 20, '~'); // 'Hello, how are you~'
```

### indent(string $s, int $level=1, string $char="\t"): string

Indents multiline text.

```php
Strings::indent('Nette');         // "\tNette"
Strings::indent('Nette', 2, '+'); // '++Nette'
```

### padLeft / padRight

```php
Strings::padLeft('Nette', 8, '+*');  // '+*+Nette'
Strings::padRight('Nette', 8, '+*'); // 'Nette+*+'
```

### substring(string $s, int $start, ?int $length=null): string

UTF-8 aware substring. Negative start counts from end.

```php
Strings::substring('Nette Framework', 0, 5); // 'Nette'
Strings::substring('Nette Framework', 6);    // 'Framework'
Strings::substring('Nette Framework', -4);   // 'work'
```

### reverse(string $s): string

```php
Strings::reverse('Nette'); // 'etteN'
```

### length(string $s): int

Returns character count (not bytes).

```php
Strings::length('Nette');   // 5
Strings::length('červená'); // 7
```

---

## String Searching

### before(string $haystack, string $needle, int $nth=1): ?string

Returns portion before nth occurrence. Negative $nth searches from end.

```php
Strings::before('Nette_is_great', '_', 1);  // 'Nette'
Strings::before('Nette_is_great', '_', -2); // 'Nette'
```

### after(string $haystack, string $needle, int $nth=1): ?string

Returns portion after nth occurrence.

```php
Strings::after('Nette_is_great', '_', 2);  // 'great'
Strings::after('Nette_is_great', '_', -1); // 'great'
```

### indexOf(string $haystack, string $needle, int $nth=1): ?int

Returns character position of nth occurrence.

```php
Strings::indexOf('abc abc abc', 'abc', 2);  // 4
Strings::indexOf('abc abc abc', 'abc', -1); // 8
```

### compare(string $left, string $right, ?int $length=null): bool

Case-insensitive comparison. Negative length compares from end.

```php
Strings::compare('Nette', 'nette');     // true
Strings::compare('Nette', 'next', 2);   // true (first 2 chars)
Strings::compare('Nette', 'Latte', -2); // true (last 2 chars)
```

### findPrefix(...$strings): string

Finds common prefix.

```php
Strings::findPrefix('prefix-a', 'prefix-bb', 'prefix-c'); // 'prefix-'
```

---

## Encoding

### fixEncoding(string $s): string

Removes invalid UTF-8 characters.

### toAscii(string $s): string

Converts UTF-8 to ASCII (removes diacritics). Requires `intl` extension.

```php
Strings::toAscii('žluťoučký kůň'); // 'zlutoucky kun'
```

### chr(int $code): string

Returns UTF-8 character from code point.

```php
Strings::chr(0xA9); // '©'
```

### ord(string $char): int

Returns code point of UTF-8 character.

```php
Strings::ord('©'); // 169
```

---

## Regular Expressions

All regex functions throw `Nette\RegexpException` on error.

### split(string $subject, string $pattern, ...): array

Splits string by regex.

```php
Strings::split('hello, world', '~,\s*~');
// ['hello', 'world']

Strings::split('hello, world', '~(,)\s*~');
// ['hello', ',', 'world'] (captures included)

// Options
Strings::split($s, $pattern, skipEmpty: true);  // skip empty items
Strings::split($s, $pattern, limit: 2);         // limit splits
Strings::split($s, $pattern, utf8: true);       // Unicode mode
Strings::split($s, $pattern, captureOffset: true); // include positions
```

### match(string $subject, string $pattern, ...): ?array

Searches for first match.

```php
Strings::match('hello!', '~\w+(!+)~');
// ['hello!', '!']

Strings::match('hello!', '~X~');
// null

// Options
Strings::match($s, $pattern, utf8: true);           // Unicode mode
Strings::match($s, $pattern, offset: 5);            // start position
Strings::match($s, $pattern, unmatchedAsNull: true); // null for unmatched groups
Strings::match($s, $pattern, captureOffset: true);   // include positions
```

### matchAll(string $subject, string $pattern, ...): array|Generator

Searches for all matches.

```php
Strings::matchAll('hello, world!!', '~\w+(!+)?~');
// [['hello'], ['world!!', '!!']]

// Options
Strings::matchAll($s, $pattern, patternOrder: true); // group by subpattern
Strings::matchAll($s, $pattern, lazy: true);         // returns Generator
Strings::matchAll($s, $pattern, utf8: true);
Strings::matchAll($s, $pattern, unmatchedAsNull: true);
Strings::matchAll($s, $pattern, captureOffset: true);
```

### replace(string $subject, string|array $pattern, string|callable $replacement='', ...): string

Replaces matches.

```php
Strings::replace('hello, world!', '~\w+~', '--');
// '--, --!'

Strings::replace('hello, world!', '~\w+~', fn($m) => strrev($m[0]));
// 'olleh, dlrow!'

// Multiple patterns
Strings::replace('hello, world!', [
    '~\w+~' => '--',
    '~,\s+~' => ' ',
]);
// '-- --!'

// Options
Strings::replace($s, $pattern, $repl, limit: 1);
Strings::replace($s, $pattern, $repl, utf8: true);
Strings::replace($s, $pattern, $callback, captureOffset: true);
Strings::replace($s, $pattern, $callback, unmatchedAsNull: true);
```

---

## UTF-8 Mode in Regex

The `utf8: true` parameter enables Unicode matching:

```php
// Without UTF-8
Strings::match('žlutý kůň', '~\w+~');
// ['lut'] (only ASCII)

// With UTF-8
Strings::match('žlutý kůň', '~\w+~', utf8: true);
// ['žlutý'] (Unicode word characters)
```

---

## Lazy Matching

For large strings, use `lazy: true` to get a Generator instead of array:

```php
$matches = Strings::matchAll($largeText, '~\w+~', lazy: true);
foreach ($matches as $match) {
    echo "Found: $match[0]\n";
    // Can break early to save processing
}
```
