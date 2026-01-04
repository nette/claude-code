# Finder

Class `Nette\Utils\Finder` for searching files and directories.

```php
use Nette\Utils\Finder;
```

---

## Basic Usage

```php
// Find files with specific extensions
foreach (Finder::findFiles(['*.txt', '*.md']) as $name => $file) {
    echo $file; // FileInfo object
}

// Find directories
foreach (Finder::findDirectories('vendor') as $dir) {
    echo $dir;
}

// Find both files and directories
foreach (Finder::find() as $item) {
    echo $item;
}
```

---

## What to Search

```php
// Static methods
Finder::findFiles('*.php');      // only files
Finder::findDirectories('src');  // only directories
Finder::find();                  // everything

// Instance methods for combining
Finder::findDirectories('vendor')
    ->files(['*.php', '*.phpt']); // directories + PHP files

// Or create instance first
(new Finder)
    ->directories()
    ->files('*.php');
```

---

## Where to Search

```php
// in() - search only in directory (not recursive)
Finder::findFiles('*.php')
    ->in('src');

// from() - search recursively
Finder::findFiles('*.php')
    ->from('src');

// Current directory recursively
Finder::findFiles('*.php')
    ->from('.');

// Multiple directories
Finder::findFiles('*.php')
    ->in(['src', 'tests'])
    ->from('vendor');

// Absolute paths
Finder::findFiles('*.php')
    ->in('/var/www/html');
```

---

## Wildcards

| Pattern | Matches |
|---------|---------|
| `*` | Any characters except `/` |
| `**` | Any characters including `/` (recursive) |
| `?` | Single character except `/` |
| `[a-z]` | Character from range |
| `[!a-z]` | Character NOT in range |

### Examples

```php
'img/?.png'              // single-char name: 0.png, x.png
'logs/[0-9][0-9][0-9][0-9]-[01][0-9]-[0-3][0-9].log' // YYYY-MM-DD
'src/**/tests/*'         // tests in any subdirectory
'docs/**.md'             // all .md in docs tree
```

### Wildcards in Path

```php
Finder::findFiles('*.php')
    ->from('src/**/tests'); // PHP files in any tests/ under src/
```

---

## Excluding

```php
// Exclude files matching pattern
Finder::findFiles('*.txt')
    ->exclude('*X*');        // skip files with X in name

// Exclude directories from traversal
Finder::findFiles('*.php')
    ->from($dir)
    ->exclude('temp', '.git');
```

---

## Filtering

### By Size

```php
Finder::findFiles('*.php')
    ->size('>=', 100)    // at least 100 bytes
    ->size('<=', 200);   // at most 200 bytes
```

### By Date

```php
Finder::findFiles('*.php')
    ->date('>', '-2 weeks')  // modified in last 2 weeks
    ->from($dir);
```

Operators: `>`, `>=`, `<`, `<=`, `=`, `!=`, `<>`

### Custom Filter

```php
Finder::findFiles('*.php')
    ->filter(fn($file) => stripos($file->read(), 'Nette') !== false);
```

---

## Depth Control

```php
// Limit recursion depth
Finder::findFiles('*.php')
    ->from('.')
    ->limitDepth(1);     // only first level subdirectories

// Custom descent filter
Finder::findFiles('*.php')
    ->descentFilter(fn($file) => $file->getBasename() !== 'temp');
```

---

## Sorting

```php
// By name (natural sort: foo1 before foo10)
$finder->sortByName();

// Custom sort
$finder->sortBy(fn($a, $b) => $a->getSize() <=> $b->getSize());
```

---

## Traversal Order

```php
// Default: parent directories first
// To get children first:
$finder->childFirst();
```

---

## Multiple Searches

```php
($finder = new Finder)
    ->files('*.php')
    ->from('src')
    ->append()           // start new search
    ->files('*.md')
    ->from('docs')
    ->append()
    ->files('*.json');   // current directory

// Or append specific files
$finder = Finder::findFiles('*.txt')
    ->append(__FILE__);
```

---

## FileInfo

Each result is a `Nette\Utils\FileInfo` object (extends `SplFileInfo`):

```php
foreach (Finder::findFiles('*.jpg')->from('.') as $file) {
    $file->getRealPath();         // absolute path
    $file->getRelativePathname(); // relative to search root
    $file->getBasename();         // filename
    $file->getSize();             // size in bytes
    $file->getMTime();            // modification timestamp

    // Read/write content
    $content = $file->read();
    $file->write($newContent);
}
```

---

## Getting Results as Array

```php
// Lazy iteration (memory efficient for many files)
foreach ($finder as $file) { ... }

// Collect to array
$files = $finder->findFiles('*.php')->collect();
// Returns array of FileInfo objects
```
