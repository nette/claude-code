# Arrays

Static class `Nette\Utils\Arrays` for working with arrays.

```php
use Nette\Utils\Arrays;
```

---

## Key Access

### get(array $array, string|int|array $key, mixed $default=null): mixed

Returns `$array[$key]`. Throws exception if key doesn't exist, unless default is provided.

```php
$value = Arrays::get($array, 'foo');           // throws if missing
$value = Arrays::get($array, 'foo', 'bar');    // returns 'bar' if missing

// Nested access with array path
$array = ['color' => ['favorite' => 'red']];
$value = Arrays::get($array, ['color', 'favorite']); // 'red'
```

### getRef(array &$array, string|int|array $key): mixed

Gets reference to array item. Creates with null if missing.

```php
$valueRef = &Arrays::getRef($array, 'foo');
$valueRef = &Arrays::getRef($array, ['color', 'favorite']); // nested
```

### pick(array &$array, string|int $key, mixed $default=null): mixed

Returns and removes item from array.

```php
$array = [1 => 'foo', 'x' => 'bar'];
$a = Arrays::pick($array, 'x');        // 'bar', removes from $array
$b = Arrays::pick($array, 'missing', 'default'); // 'default'
```

---

## Searching

### contains(array $array, $value): bool

Tests for value presence using strict comparison (`===`).

```php
Arrays::contains([1, 2, 3], 1);    // true
Arrays::contains(['1', false], 1); // false (strict)
```

### first(array $array, ?callable $predicate=null, ?callable $else=null): mixed

Returns first item (matching predicate if given).

```php
Arrays::first([1, 2, 3]);                   // 1
Arrays::first([1, 2, 3], fn($v) => $v > 2); // 3
Arrays::first([], else: fn() => false);     // false
```

### last(array $array, ?callable $predicate=null, ?callable $else=null): mixed

Returns last item (matching predicate if given).

```php
Arrays::last([1, 2, 3]);                   // 3
Arrays::last([1, 2, 3], fn($v) => $v < 3); // 2
```

### firstKey / lastKey

Returns key of first/last item (matching predicate if given).

```php
Arrays::firstKey(['a' => 1, 'b' => 2]); // 'a'
Arrays::lastKey(['a' => 1, 'b' => 2]);  // 'b'
```

### getKeyOffset(array $array, string|int $key): ?int

Returns zero-indexed position of key.

```php
$array = ['first' => 10, 'second' => 20];
Arrays::getKeyOffset($array, 'second'); // 1
```

---

## Testing

### every(array $array, callable $predicate): bool

Tests if all elements pass the predicate.

```php
$array = [1, 30, 39, 29, 10, 13];
Arrays::every($array, fn($v) => $v < 40); // true
```

### some(array $array, callable $predicate): bool

Tests if at least one element passes the predicate.

```php
$array = [1, 2, 3, 4];
Arrays::some($array, fn($v) => $v % 2 === 0); // true
```

### isList(array $array): bool

Checks if array is indexed list (keys: 0, 1, 2...).

```php
Arrays::isList(['a', 'b', 'c']); // true
Arrays::isList([4 => 1, 2, 3]); // false
```

---

## Transformation

### map(array $array, callable $transformer): array

Transforms values. Callback: `function ($value, $key, array $array): mixed`.

```php
$array = ['foo', 'bar', 'baz'];
Arrays::map($array, fn($v) => $v . $v);
// ['foofoo', 'barbar', 'bazbaz']
```

### mapWithKeys(array $array, callable $transformer): array

Transforms both keys and values. Return `[$newKey, $newValue]` or `null` to skip.

```php
$array = ['a' => 1, 'b' => 2];
Arrays::mapWithKeys($array, fn($v, $k) => $v > 1 ? [$v * 2, strtoupper($k)] : null);
// [4 => 'B']
```

### filter(array $array, callable $predicate): array

Returns items matching predicate.

```php
Arrays::filter(['a' => 1, 'b' => 2, 'c' => 3], fn($v) => $v < 3);
// ['a' => 1, 'b' => 2]
```

### flatten(array $array, bool $preserveKeys=false): array

Flattens multi-level array into single level.

```php
Arrays::flatten([1, 2, [3, 4, [5, 6]]]); // [1, 2, 3, 4, 5, 6]
```

### grep(array $array, string $pattern, bool $invert=false): array

Returns items matching regex pattern.

```php
Arrays::grep($array, '~^\d+$~'); // only digits
```

---

## Insertion & Removal

### insertBefore(array &$array, string|int|null $key, array $inserted): void

Inserts before key (or at beginning if null).

```php
$array = ['first' => 10, 'second' => 20];
Arrays::insertBefore($array, 'first', ['hello' => 'world']);
// ['hello' => 'world', 'first' => 10, 'second' => 20]
```

### insertAfter(array &$array, string|int|null $key, array $inserted): void

Inserts after key (or at end if null).

### renameKey(array &$array, string|int $oldKey, string|int $newKey): bool

Renames a key in array.

```php
$array = ['first' => 10, 'second' => 20];
Arrays::renameKey($array, 'first', 'renamed');
// ['renamed' => 10, 'second' => 20]
```

---

## Merging & Normalization

### mergeTree(array $array1, array $array2): array

Recursively merges arrays. Keeps first array values on collision.

```php
$array1 = ['color' => ['favorite' => 'red'], 5];
$array2 = [10, 'color' => ['favorite' => 'green', 'blue']];
Arrays::mergeTree($array1, $array2);
// ['color' => ['favorite' => 'red', 'blue'], 5]
```

### normalize(array $array, ?string $filling=null): array

Normalizes to associative array. Numeric keys become values.

```php
Arrays::normalize([1 => 'first', 'a' => 'second']);
// ['first' => null, 'a' => 'second']

Arrays::normalize([1 => 'first', 'a' => 'second'], 'foobar');
// ['first' => 'foobar', 'a' => 'second']
```

### associate(array $array, mixed $path): array|stdClass

Transforms array according to path with operators `=`, `->`, `|`, `[]`.

```php
$arr = [
    ['name' => 'John', 'age' => 11],
    ['name' => 'Mary', 'age' => null],
];

Arrays::associate($arr, 'name');
// ['John' => ['name' => 'John', 'age' => 11], 'Mary' => [...]]

Arrays::associate($arr, 'name=age');
// ['John' => 11, 'Mary' => null]
```

---

## Utilities

### invoke(iterable $callbacks, ...$args): array

Invokes all callbacks and returns results.

```php
$callbacks = [
    '+' => fn($a, $b) => $a + $b,
    '*' => fn($a, $b) => $a * $b,
];
Arrays::invoke($callbacks, 5, 11); // ['+' => 16, '*' => 55]
```

### invokeMethod(iterable $objects, string $method, ...$args): array

Invokes method on each object.

### toObject(iterable $array, object $object): object

Copies array elements to object properties.

### toKey(mixed $key): string|int

Converts value to array key.

### wrap(array $array, string $prefix='', string $suffix=''): array

Wraps each item with prefix and suffix.

```php
Arrays::wrap(['red', 'green'], '<<', '>>');
// ['<<red>>', '<<green>>']
```

---

# ArrayHash

Object that can be accessed like array. Extends `stdClass`.

```php
$hash = new Nette\Utils\ArrayHash;
$hash['foo'] = 123;
$hash->bar = 456;  // both syntaxes work
echo $hash->foo;   // 123

// Create from array (recursive)
$hash = Nette\Utils\ArrayHash::from(['foo' => 123, 'inner' => ['a' => 'b']]);
$hash->inner->a;   // 'b'

// Non-recursive
$hash = Nette\Utils\ArrayHash::from($array, recursive: false);

// Convert back
$array = (array) $hash;
```

---

# ArrayList

Array where indexes are only integers starting from 0.

```php
$list = new Nette\Utils\ArrayList;
$list[] = 'a';
$list[] = 'b';
// ArrayList(0 => 'a', 1 => 'b')

// Throws on invalid key access
echo $list[-1];     // Nette\OutOfRangeException
$list['foo'] = 'x'; // Nette\OutOfRangeException

// Add to beginning
$list->prepend('first');

// Remove renumbers keys
unset($list[1]);
// ArrayList(0 => 'first', 1 => 'b')
```
