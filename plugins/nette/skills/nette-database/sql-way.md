# SQL Way Reference

Direct SQL queries for full control over database operations.

## Basic Queries

```php
$result = $database->query('SELECT * FROM users');

foreach ($result as $row) {
	echo $row->id;
	echo $row->name;
}
```

## Parameterized Queries

**Always use parameters to prevent SQL injection:**

```php
// Single parameter
$database->query('SELECT * FROM users WHERE name = ?', $name);

// Multiple parameters
$database->query('SELECT * FROM users WHERE name = ? AND age > ?', $name, $age);

// Interleaved
$database->query('SELECT * FROM users WHERE name = ?', $name, 'AND age > ?', $age);
```

## WHERE Conditions

Pass associative array for automatic operator selection:

```php
$database->query('SELECT * FROM users WHERE', [
	'name' => 'John',           // = 'John'
	'active' => true,           // = 1
	'category_id' => [1, 2, 3], // IN (1, 2, 3)
	'email' => null,            // IS NULL
	'age >' => 25,              // > 25
	'name LIKE' => '%John%',    // LIKE '%John%'
]);

// Negation
$database->query('SELECT * FROM products WHERE', [
	'name NOT' => 'Laptop',     // <> 'Laptop'
	'status NOT' => [1, 2],     // NOT IN (1, 2)
	'deleted NOT' => null,      // IS NOT NULL
]);
```

## OR Conditions

```php
$database->query('SELECT * FROM users WHERE ?or', [
	'name' => 'John',
	'email' => 'john@example.com',
]);
// WHERE `name` = 'John' OR `email` = 'john@example.com'
```

## ORDER BY

```php
$database->query('SELECT * FROM users ORDER BY', [
	'name' => true,   // ASC
	'age' => false,   // DESC
]);
```

## INSERT

```php
$values = [
	'name' => 'John',
	'email' => 'john@example.com',
];

$database->query('INSERT INTO users ?', $values);
$userId = $database->getInsertId();
```

Multiple rows:

```php
$database->query('INSERT INTO users ?', [
	['name' => 'John', 'email' => 'john@example.com'],
	['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

## UPDATE

```php
$database->query('UPDATE users SET ? WHERE id = ?', [
	'name' => 'John Smith',
	'updated_at' => new DateTime,
], $id);

// Increment/decrement
$database->query('UPDATE users SET ? WHERE id = ?', [
	'login_count+=' => 1,
], $id);
```

## UPSERT (ON DUPLICATE KEY UPDATE)

```php
$values = ['name' => $name, 'email' => $email];

$database->query(
	'INSERT INTO users ? ON DUPLICATE KEY UPDATE ?',
	$values + ['id' => $id],
	$values,
);
```

## DELETE

```php
$count = $database->query('DELETE FROM users WHERE id = ?', $id)
	->getRowCount();
```

## SQL Hints

| Hint | Description | Auto-used for |
|------|-------------|---------------|
| `?name` | Table/column name (quoted) | - |
| `?values` | `(col, ...) VALUES (val, ...)` | `INSERT ?` |
| `?set` | `col = val, ...` | `SET ?`, `UPDATE ?` |
| `?and` | Conditions with AND | `WHERE ?` |
| `?or` | Conditions with OR | - |
| `?order` | ORDER BY clause | `ORDER BY ?` |

```php
// Dynamic table/column names
$database->query('SELECT ?name FROM ?name', $column, $table);

// Explicit OR
$database->query('SELECT * FROM users WHERE ?or', [...]);
```

## Special Values

```php
$database->query('INSERT INTO articles ?', [
	'title' => 'My Article',
	'created_at' => new DateTime,           // Converted to DB format
	'content' => fopen('file.txt', 'r'),    // Binary content
	'status' => Status::Draft,              // Enum value
	'uuid' => $database::literal('UUID()'), // SQL literal
]);
```

## SQL Literals

For raw SQL expressions:

```php
$database->query('SELECT * FROM users WHERE', [
	'name' => $name,
	'year >' => $database::literal('YEAR()'),
]);

// With parameters
$database->query('SELECT * FROM users WHERE', [
	$database::literal('year > ? AND year < ?', $min, $max),
]);
```

## Fetching Data

### Shortcuts

| Method | Description |
|--------|-------------|
| `fetch($sql, ...)` | First row or null |
| `fetchAll($sql, ...)` | All rows as array |
| `fetchPairs($sql, ...)` | Associative array |
| `fetchField($sql, ...)` | First column of first row |

```php
$user = $database->fetch('SELECT * FROM users WHERE id = ?', $id);

$users = $database->fetchAll('SELECT * FROM users WHERE active = ?', true);

$names = $database->fetchPairs('SELECT id, name FROM users');
// [1 => 'John', 2 => 'Jane']

$count = $database->fetchField('SELECT COUNT(*) FROM users');
```

### ResultSet Methods

```php
$result = $database->query('SELECT * FROM users');

// Iterate
foreach ($result as $row) { ... }

// Single row
$row = $result->fetch();

// All rows
$rows = $result->fetchAll();

// Key-value pairs
$pairs = $result->fetchPairs('id', 'name');

// First column value
$value = $result->fetchField();

// Affected rows (UPDATE, DELETE)
$count = $result->getRowCount();
```

## Query Info

```php
// Last query
echo $database->getLastQueryString();

// Result info
$result = $database->query('SELECT * FROM users');
echo $result->getQueryString();
echo $result->getTime();  // Execution time in seconds

// Column types
$types = $result->getColumnTypes();
foreach ($types as $col => $type) {
	echo "$col: $type->type";
}
```

## Query Logging

```php
$database->onQuery[] = function ($database, $result) use ($logger) {
	$logger->info('Query: ' . $result->getQueryString());
	$logger->info('Time: ' . $result->getTime() . 's');

	if ($result->getRowCount() > 1000) {
		$logger->warning('Large result set');
	}
};
```

## Transactions

```php
$database->beginTransaction();

try {
	$database->query('UPDATE accounts SET balance -= ? WHERE id = ?', 100, 1);
	$database->query('UPDATE accounts SET balance += ? WHERE id = ?', 100, 2);
	$database->commit();
} catch (\Exception $e) {
	$database->rollBack();
	throw $e;
}

// Or use transaction() helper
$database->transaction(function ($database) {
	$database->query('...');
	$database->query('...');
});
```
