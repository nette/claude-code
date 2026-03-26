---
name: nette-database
description: Invoke before writing database queries or working with Selection API, ActiveRow in Nette. Use when creating entity classes, configuring database connections, writing queries, fetching data, using joins, or designing Row classes. Also consult when deciding between Selection API and raw SQL, or setting up database configuration in .neon files.
---

## Database

Uses Nette Database, typically with MySQL, PostgreSQL or SQLite as the backend.

```shell
composer require nette/database
```

See [the Explorer API reference](references/explorer.md) for the full ActiveRow/Selection API.
See [the SQL query reference](references/sql-way.md) for direct SQL queries.

### Database Conventions

- Table names use **singular form** (e.g., `user` not `users`)
- Use TINYINT(1) for booleans
- Use `id` for primary keys
- Character encoding: `utf8mb4` with appropriate collation (e.g. `utf8mb4_0900_ai_ci`, or `utf8mb4_cs_0900_ai_ci` for Czech)
- Standard timestamp fields:
  - `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
  - never use TIMESTAMP for date/time fields


### Database Explorer

Extends Nette Database Explorer to automatically map database tables to typed entity classes.

**Core benefit:** Zero-configuration entity mapping with full IDE support.
**How it works:** Converts table names (snake_case) to entity class names (PascalCase + Row suffix).

### Entity Design Strategy

All entities in `App\Entity` with consistent `Row` suffix:

- `product` table → `ProductRow`
- `order_item` table → `OrderItemRow`
- `variant_expiration` table → `VariantExpirationRow`

**Why flat:** Entities are data structures that cross domain boundaries. A `ProductRow` might be used in catalog, orders, inventory, and reporting contexts. Subdividing entities by domain forces you to either pick one arbitrary "home" domain or duplicate references.

#### Entity Organization

**All entities in single App\Entity namespace** - avoid domain subdivision:

```
app/Entity/
├── ProductRow.php          ← Core business entities
├── OrderItemRow.php        ← Relationship entities
└── StockTransferRow.php    ← Operational entities
```

#### Entity Documentation Patterns

```php
use Nette\Database\Table;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read bool $active
 * @property-read ?CategoryRow $category        ← nullable relationship
 * @property-read UserRow $author               ← required relationship
 */
final class ProductRow extends Table\ActiveRow
{
}
```

**Documentation rules:**
1. Document ALL accessible properties (including inherited id)
2. Use nullable types for optional foreign keys
3. Include relationship properties for IDE navigation
4. Match database schema exactly

#### Entity Relationships in phpDoc

**Foreign key patterns:**
- `@property-read ?CategoryRow $category` for optional relationships
- `@property-read UserRow $author` for required relationships
- `@property-read Selection<OrderItemRow> $order_items` for back-references

**Naming convention:** Follow Nette Database relationship naming (foreign key without _id suffix).

### When to Use Selection API

**Use for:**
- Simple filtering and sorting
- Standard CRUD operations
- Queries that benefit from lazy loading
- When you need to chain conditions dynamically

```php
return $this->db->table('product')
	->where('active', true)
	->where('category_id', $categoryId)
	->order('name');
```

### When to Use Raw SQL

**Use for:**
- Complex analytics and reporting
- Recursive queries (WITH RECURSIVE)
- Performance-critical queries
- Complex joins that are awkward in Selection API

```php
return $this->db->query('
	WITH RECURSIVE category_tree AS (...)
	SELECT ...
', $params)->fetchAll();
```

### Query Building Patterns

Build queries by progressive refinement – start with a base method, then add conditions. Always use generic types for Selection returns:

```php
/** @return Selection<ProductRow> */
public function getProducts(): Selection
{
	return $this->db->table('product');
}

/** @return Selection<ProductRow> */
public function getActiveProducts(): Selection
{
	return $this->getProducts()->where('active', true);
}

/** @return Selection<ProductRow> */
public function getProductsInCategory(int $categoryId): Selection
{
	return $this->getActiveProducts()
		->where(':product_category.category_id', $categoryId);
}
```

**Benefits:** Reusable base queries, clear evolution of filtering logic, easy testing. Full IDE support, type safety, clear contracts.

### Relationship Navigation

**Use colon notation for efficient joins:**

```php
// Forward relationship (via foreign key)
->where('category.slug', $categorySlug)

// Back-reference (reverse relationship)
->where(':order_item.quantity >', 1)

// Deep relationships
->where('category.parent.name', 'Root Category')
```

### Fetching Strategies by Use Case

**Single optional result:** `->fetch()`
**All results as array:** `->fetchAll()`
**Key-value pairs:** `->fetchPairs('key_column', 'value_column')`
**Single scalar value:** `->fetchField()` (first column of first row)
**Count only:** `->count('*')`

**Structured data with fetchAssoc:**

```php
// Key by column value
$byId = $db->table('product')->fetchAssoc('id');
// [1 => ProductRow, 2 => ProductRow, ...]

// Group by column
$byCategory = $db->table('product')->fetchAssoc('category_id[]');
// [5 => [ProductRow, ProductRow], 8 => [ProductRow, ...]]

// Nested grouping
$nested = $db->table('product')->fetchAssoc('category_id|active');
// [5 => [true => ProductRow, false => ProductRow], ...]
```

The path string uses `[]` for array grouping, `|` for nested keys, and `=` to extract a single value.

### Schema and Constraints

**Use direct SQL migrations** rather than ORM-style migrations – store schema in `sql/db.sql` with manual migration scripts. Rely on database constraints (foreign keys, unique, check) for data integrity and handle constraint violations in services with meaningful business exceptions.

### Transactions

Wrap multi-step writes in transactions to ensure consistency:

```php
$this->db->transaction(function () use ($data, $items) {
	$order = $this->db->table('order')->insert($data);
	foreach ($items as $item) {
		$order->related('order_item')->insert($item);
	}
});
```

The callback approach automatically commits on success and rolls back on exception.

### Anti-Patterns to Avoid

**Don't create separate Repository classes** – in Nette, services combine data access with business logic. A separate repository layer adds indirection without benefit because Nette Database Explorer already provides a clean query API. The service IS the repository.

**Don't use Selection API for complex queries** – raw SQL is cleaner for analytics, reporting, and recursive queries. Selection API excels at CRUD and simple filtering; forcing complex JOINs through it creates hard-to-read code.

**Don't fetch more data than needed** – use appropriate fetching methods (`fetchPairs` for dropdowns, `count('*')` for pagination) and SELECT only required columns for large datasets.

### Error Handling

**Transform database errors to business exceptions:**

```php
try {
	$customer->update(['email' => $newEmail]);
} catch (Nette\Database\UniqueConstraintViolationException) {
	throw new EmailAlreadyExistsException();
}
```

**Handle at service boundary** – presenters should receive business exceptions, not database exceptions. This keeps the UI layer independent of the storage layer and produces meaningful error messages.

### Database Configuration

```neon
database:
	dsn: 'mysql:host=127.0.0.1;dbname=myapp'
	user: root
	password: secret
	options:
		lazy: true              # Connect on first query
		charset: utf8mb4        # Default
		convertBoolean: true    # TINYINT(1) to bool
		newDateTime: true       # Return DateTimeImmutable
```

Multiple connections:

```neon
database:
	main:
		dsn: 'mysql:host=127.0.0.1;dbname=app'
		user: root
		password: secret

	logs:
		dsn: 'mysql:host=127.0.0.1;dbname=logs'
		user: logs
		password: secret
		autowired: false  # Must reference explicitly
```

Reference non-autowired connection:

```neon
services:
	- LogService(@database.logs.connection)
```

### Online Documentation

For detailed information, use WebFetch on these URLs:

- [Database Core](https://doc.nette.org/en/database/core) – SQL queries and Connection API
- [Database Explorer](https://doc.nette.org/en/database/explorer) – Selection/ActiveRow API
- [Database Configuration](https://doc.nette.org/en/database/configuration) – connection setup
