# Database Explorer Reference

## Basic Usage

```php
$books = $explorer->table('book');

foreach ($books as $book) {
	echo $book->title;
	echo $book->author->name;  // automatic JOIN
}
```

## Selection Methods

### Filtering

| Method | Description |
|--------|-------------|
| `where($condition, ...$params)` | Add WHERE condition (AND) |
| `whereOr(array $conditions)` | Add WHERE conditions (OR) |
| `wherePrimary($value)` | Filter by primary key |

```php
// Simple conditions
$table->where('id', $value);           // WHERE id = ?
$table->where('id', null);             // WHERE id IS NULL
$table->where('id', [1, 2, 3]);        // WHERE id IN (1, 2, 3)
$table->where('id >', 10);             // WHERE id > 10

// Multiple conditions (AND)
$table->where('active', true)->where('published', true);

// Array conditions
$table->where([
	'status' => 'active',
	'category_id' => [1, 2, 3],
]);

// OR conditions
$table->whereOr([
	'status' => 'active',
	'featured' => true,
]);

// By primary key
$table->wherePrimary(123);
$table->wherePrimary([1, 2, 3]);
```

### Sorting and Limiting

| Method | Description |
|--------|-------------|
| `order($columns, ...$params)` | ORDER BY |
| `limit($limit, $offset = null)` | LIMIT and OFFSET |
| `page($page, $itemsPerPage, &$total)` | Pagination |

```php
$table->order('created DESC');
$table->order('priority DESC, created');
$table->order('status = ? DESC', 'featured');

$table->limit(10);
$table->limit(10, 20);  // 10 items, skip 20

$table->page(3, 10, $totalPages);  // page 3, 10 per page
```

### Selecting Columns

| Method | Description |
|--------|-------------|
| `select($columns, ...$params)` | Specify columns |
| `group($columns, ...$params)` | GROUP BY |
| `having($condition, ...$params)` | HAVING |

```php
$table->select('id, title, author_id');
$table->select('*, DATE_FORMAT(created, ?) AS formatted', '%Y-%m-%d');

$table->select('category_id, COUNT(*) AS count')
	->group('category_id')
	->having('count > ?', 5);
```

## Reading Data

| Method | Description |
|--------|-------------|
| `foreach` | Iterate all rows |
| `get($key)` | Get row by primary key |
| `fetch()` | Get current row, advance pointer |
| `fetchAll()` | Get all rows as array |
| `fetchPairs($key, $value)` | Get associative array |
| `count()` | Count rows |

```php
// By primary key
$book = $explorer->table('book')->get(123);

// Iteration
foreach ($explorer->table('book') as $book) {
	echo $book->title;
}

// Key-value pairs
$countries = $explorer->table('country')
	->fetchPairs('code', 'name');
// ['CZ' => 'Czech Republic', 'SK' => 'Slovakia']

// All rows as array
$books = $explorer->table('book')
	->where('active', true)
	->fetchAll();

// Count
$count = $explorer->table('book')->where('active', true)->count('*');
```

## Aggregation

| Method | Description |
|--------|-------------|
| `count($expr)` | COUNT |
| `min($expr)` | MIN |
| `max($expr)` | MAX |
| `sum($expr)` | SUM |
| `aggregation($func)` | Custom aggregate |

```php
$count = $table->count('*');
$maxPrice = $table->where('active', true)->max('price');
$totalValue = $table->sum('price * quantity');
$avgRating = $table->aggregation('AVG(rating)');
```

## Relationships

### Parent Table (belongs to)

```php
$book = $explorer->table('book')->get(1);

// Access via property (foreign key without _id)
echo $book->author->name;      // via author_id
echo $book->translator?->name; // via translator_id (nullable)

// Using ref()
echo $book->ref('author', 'author_id')->name;
```

### Child Table (has many)

```php
$author = $explorer->table('author')->get(1);

// Books written by author
foreach ($author->related('book.author_id') as $book) {
	echo $book->title;
}

// Shorthand (auto-detects column)
foreach ($author->related('book') as $book) {
	echo $book->title;
}
```

### Many-to-Many

```php
$book = $explorer->table('book')->get(1);

// Through junction table
foreach ($book->related('book_tag') as $bookTag) {
	echo $bookTag->tag->name;
}
```

### Querying Related Tables

```php
// Dot notation (forward relationship)
$books->where('author.name LIKE ?', 'John%');
$books->order('author.name');

// Colon notation (back-reference)
$authors->where(':book.title LIKE ?', '%PHP%');

// With explicit column
$authors->where(':book(translator_id).title LIKE ?', '%PHP%');

// Chain relationships
$authors->where(':book:book_tag.tag.name', 'PHP');
```

## Insert, Update, Delete

### Insert

```php
// Single record
$row = $explorer->table('book')->insert([
	'title' => 'New Book',
	'author_id' => 1,
	'created_at' => new DateTime,
]);
echo $row->id;  // auto-generated ID

// Multiple records
$count = $explorer->table('book')->insert([
	['title' => 'Book 1', 'author_id' => 1],
	['title' => 'Book 2', 'author_id' => 2],
]);
```

### Update

```php
// Via Selection
$affected = $explorer->table('book')
	->where('id', 10)
	->update(['title' => 'Updated Title']);

// Increment/decrement
$explorer->table('book')
	->where('id', 10)
	->update([
		'views+=' => 1,
		'stock-=' => 1,
	]);

// Via ActiveRow
$book = $explorer->table('book')->get(10);
$book->update(['title' => 'New Title']);
```

### Delete

```php
// Via Selection
$deleted = $explorer->table('book')
	->where('id', 10)
	->delete();

// Via ActiveRow
$book = $explorer->table('book')->get(10);
$book->delete();
```

## ActiveRow Methods

```php
$row = $explorer->table('book')->get(1);

$row->id;                    // Column access
$row->author;                // Related record (parent)
$row->related('comment');    // Related records (children)
$row->toArray();             // Convert to array
$row->update([...]);         // Update this row
$row->delete();              // Delete this row
```

## JOIN Conditions

```php
// Extend JOIN with additional conditions
$books = $explorer->table('book')
	->joinWhere('translator', 'translator.name', 'David');
// LEFT JOIN author translator ON book.translator_id = translator.id
//   AND (translator.name = 'David')

// Table aliases
$tags = $explorer->table('tag')
	->joinWhere(':book_tag.book.author', 'book_author.born < ?', 1950)
	->alias(':book_tag.book.author', 'book_author');
```
