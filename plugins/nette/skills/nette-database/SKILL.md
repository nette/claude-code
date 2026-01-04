---
name: nette-database
description: Provides Nette Database conventions and API reference. Use when working with database entities, Selection API, ActiveRow, SQL queries, or database configuration in Nette.
---

## Database

Uses Nette Database with MySQL 8.4+ as the backend.

```shell
composer require nette/database
```

For complete Explorer API, see [explorer.md](explorer.md).
For SQL queries, see [sql-way.md](sql-way.md).

### Database Conventions

- Table names use **singular form** (e.g., `user` not `users`)
- Use TINYINT(1) for booleans
- Use `id` for primary keys
- Character encoding should be:
  - `utf8mb4_cs_0900_ai_ci` for Czech-language applications
  - `utf8mb4_0900_ai_ci` for English-language applications
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

**Why flat:** Entities are data structures that cross domain boundaries. ProductRow used in catalog, orders, inventory, and reporting contexts.

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

**Progressive refinement** - start with base methods, refine with conditions:

**Always use generic types** for Selection returns:

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

**Benefits:** Reusable base queries, clear evolution of filtering logic, easy testing.
**Benefits:** Full IDE support, type safety, clear contracts.

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
**Structured data:** `->fetchAssoc('key_column->')`
**Count only:** `->count('*')`

### Schema Management

**Use direct SQL migrations** rather than ORM-style migrations:
- Store schema in `sql/db.sql`
- Manual migration scripts for schema changes
- Version control captures schema evolution

### Database Constraints

**Rely on database constraints** for data integrity:
- Foreign key constraints for relationships
- Unique constraints for business rules
- Check constraints for data validation

**Handle constraint violations** in services with meaningful business exceptions.

### Anti-Patterns to Avoid

**Don't create separate Repository classes** - combine data access with business logic in services.
**Don't use ActiveRecord for complex queries** - raw SQL is cleaner for analytics and reporting.
**Don't fetch more data than needed** - use appropriate fetching methods and SELECT only required columns for large datasets.

### Error Handling Strategy

### Service Level Error Handling

**Transform database errors to business exceptions:**

```php
try {
	$customer->update(['email' => $newEmail]);
} catch (Nette\Database\UniqueConstraintViolationException) {
	throw new EmailAlreadyExistsException();
}
```

**Handle at service boundary** - presenters should receive business exceptions, not database exceptions.

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

---

## Online Documentation

For detailed information, fetch from doc.nette.org:

- [Database Core](https://doc.nette.org/en/database/core) - SQL queries
- [Database Explorer](https://doc.nette.org/en/database/explorer) - ORM-like API
- [Configuration](https://doc.nette.org/en/database/configuration) - connection setup
