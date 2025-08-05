# Database

Sentience offers its own Database class. It uses PDO to communicate with the database.

## 1. Supported databases

Sentience offsers the following databases:
1. MySQL
2. Postgres
3. SQLite

If you wish to add support for a new database, create a new dialect in the `src/database/dialects` folder, and add the PDO driver name to the match statement in the `DialectFactory`.

## 2. Executing queries

The Database class offers two methods to execute queries.
```
Database::unsafe(query: $query);
Database::safe(query: $query, params: [...$values]);
```

The `unsafe` method is only recommended for queries that do not hold any input data, such as create, drop, or alter queries.

The `safe` methods changes modifies the params depending on which Dialect is currently active. This will translate DateTime and booleans to their correct format.

## 3. Transactions

The Database class offers the following methods for transactions:
```
Databse::beginTransaction();
Databse::commitTransaction();
Databse::rollbackTransaction();
Databse::transactionInCallback(function (Database $database): void {
    ...
});
```

## 4. Queries

The Database class offers querybuilders for the most common queries.
- Select
- Insert
- Update
- Delete
- Create table
- Alter table
- Drop table

### 4.1 Select

```
Database::select();
```

The select query class has the following methods:
```
Select::table();
Select::distinct();
Select::columns();
Select::leftJoin();
Select::rightJoin(); // Not supported in SQLite
Select::innerJoin();
Select::join();
Select::whereEquals();
Select::whereNotEquals();
Select::whereLike();
Select::whereNotLike();
Select::whereStartsWith();
Select::whereEndsWith();
Select::whereContains();
Select::whereIn();
Select::whereNotIn();
Select::whereLessThan();
Select::whereLessThanOrEquals();
Select::whereGreaterThan();
Select::whereGreaterThanOrEquals();
Select::whereBetween();
Select::whereNotBetween();
Select::whereIsNull();
Select::whereIsNotNull();
Select::whereEmpty();
Select::whereNotEmpty();
Select::whereRegex();
Select::whereNotRegex();
Select::whereGroup();
Select::where();
Select::orWhereEquals();
Select::orWhereNotEquals();
Select::orWhereLike();
Select::orWhereNotLike();
Select::orWhereStartsWith();
Select::orWhereEndsWith();
Select::orWhereContains();
Select::orWhereIn();
Select::orWhereNotIn();
Select::orWhereLessThan();
Select::orWhereLessThanOrEquals();
Select::orWhereGreaterThan();
Select::orWhereGreaterThanOrEquals();
Select::orWhereBetween();
Select::orWhereNotBetween();
Select::orWhereIsNull();
Select::orWhereIsNotNull();
Select::orWhereEmpty();
Select::orWhereNotEmpty();
Select::orWhereRegex();
Select::orWhereNotRegex();
Select::orWhereGroup();
Select::orWhere();
Select::groupBy();
Select::having();
Select::orderByAsc();
Select::orderByDesc();
Select::limit();
Select::offset();

Select::count();
Select::exists();
```

### 4.2 Insert

```
Database::insert();
```

The insert query class has the following methods:
```
Insert::table();
Insert::values();
Insert::onConflictIgnore();
Insert::onConflictUpdate();
Insert::returning();
```

### 4.3 Update

```
Database::update();
```

The update query class has the following methods:
```
Update::table();
Update::values();
Update::whereEquals();
Update::whereNotEquals();
Update::whereLike();
Update::whereNotLike();
Update::whereStartsWith();
Update::whereEndsWith();
Update::whereContains();
Update::whereIn();
Update::whereNotIn();
Update::whereLessThan();
Update::whereLessThanOrEquals();
Update::whereGreaterThan();
Update::whereGreaterThanOrEquals();
Update::whereBetween();
Update::whereNotBetween();
Update::whereIsNull();
Update::whereIsNotNull();
Update::whereEmpty();
Update::whereNotEmpty();
Update::whereRegex();
Update::whereNotRegex();
Update::whereGroup();
Update::where();
Update::orWhereEquals();
Update::orWhereNotEquals();
Update::orWhereLike();
Update::orWhereNotLike();
Update::orWhereStartsWith();
Update::orWhereEndsWith();
Update::orWhereContains();
Update::orWhereIn();
Update::orWhereNotIn();
Update::orWhereLessThan();
Update::orWhereLessThanOrEquals();
Update::orWhereGreaterThan();
Update::orWhereGreaterThanOrEquals();
Update::orWhereBetween();
Update::orWhereNotBetween();
Update::orWhereIsNull();
Update::orWhereIsNotNull();
Update::orWhereEmpty();
Update::orWhereNotEmpty();
Update::orWhereRegex();
Update::orWhereNotRegex();
Update::orWhereGroup();
Update::orWhere();
Update::returning();
```

### 4.4 Delete

```
Database::delete();
```

The delete query class has the following methods:
```
Delete::table();
Delete::whereEquals();
Delete::whereNotEquals();
Delete::whereLike();
Delete::whereNotLike();
Delete::whereStartsWith();
Delete::whereEndsWith();
Delete::whereContains();
Delete::whereIn();
Delete::whereNotIn();
Delete::whereLessThan();
Delete::whereLessThanOrEquals();
Delete::whereGreaterThan();
Delete::whereGreaterThanOrEquals();
Delete::whereBetween();
Delete::whereNotBetween();
Delete::whereIsNull();
Delete::whereIsNotNull();
Delete::whereEmpty();
Delete::whereNotEmpty();
Delete::whereRegex();
Delete::whereNotRegex();
Delete::whereGroup();
Delete::where();
Delete::orWhereEquals();
Delete::orWhereNotEquals();
Delete::orWhereLike();
Delete::orWhereNotLike();
Delete::orWhereStartsWith();
Delete::orWhereEndsWith();
Delete::orWhereContains();
Delete::orWhereIn();
Delete::orWhereNotIn();
Delete::orWhereLessThan();
Delete::orWhereLessThanOrEquals();
Delete::orWhereGreaterThan();
Delete::orWhereGreaterThanOrEquals();
Delete::orWhereBetween();
Delete::orWhereNotBetween();
Delete::orWhereIsNull();
Delete::orWhereIsNotNull();
Delete::orWhereEmpty();
Delete::orWhereNotEmpty();
Delete::orWhereRegex();
Delete::orWhereNotRegex();
Delete::orWhereGroup();
Delete::orWhere();
Delete::returning();
```

### 4.5 Create table

```
Database::createTable();
```

The create table query class has the following methods:
```
CreateTable::table();
CreateTable::ifNotExists();
CreateTable::column();
CreateTable::primaryKeys();
CreateTable::uniqueConstraint();
CreateTable::foreignKeyConstraint();
CreateTable::constraint();
```

### 4.6 Alter table

```
Database::alterTable();
```

The alter table query class has the following methods:
```
AlterTable::table();
AlterTable::addColumn();
AlterTable::alterColumn(); // Not supported in SQLite
AlterTable::renameColumn();
AlterTable::dropColumn();
AlterTable::addUniqueConstraint();
AlterTable::addForeignKeyConstraint();
AlterTable::dropConstraint();
```

### 4.7 Drop table

```
Database::dropTable();
```

The drop table query class has the following methods:
```
DropTable::table();
DropTable::ifExists();
```

### 4.8 Universal methods

Each query class has the following methods:
```
Query::execute();   // Returns a Results object
Query::tryCatch();  // Returns a Results object, but allows you to catch the exception using a callback
Query::toRawQuery();  // Return query with values instead of placeholders
```

### 4.9 Dot notation references

Instead of using namespace.table.column, and losing out on escaped column names, you can use arrays.
```
['public', 'migrations', 'id']
```

Translates to:
```
"public"."migrations"."id'
```

The use of Raw objects is also permitted in array.
```
['public', Query::raw('migrations'), 'id']
```

Translates to:
```
"public".migrations."id'
```

## 5. Query helper methods

The Query class offers several helper methods:
```
Query::raw()                // Returns a Raw object
Query::alias()              // For table and columns
Query::escapeLikeChars()    // Escapes characters for like queries
Query::wildcard()           // Useful for quick like queries
Query::now()                // Emulates the widely known 'now()' function
```
