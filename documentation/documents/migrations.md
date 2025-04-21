# Migrations

Sentience offers a way to migrate database changes, independent of which database engine you're using.

## 1. Migration filename format

Sentience uses the following formats for migration files:
```
{YYYY}{MM}{DD}{HH}{MM}{SS}_{MIGRATION NAME IN SNAKECASE}.php
```

The easiest way to adhere to this format, is by using the `migrations:create` command.
```
php sentience.php migrations:init {MIGRATION NAME}
```

## 2. Initializing migrations

You can initialize migrations by running the following command:
```
php sentience.php migrations:init
```

The create table command uses an IF NOT EXISTS clause, so no errors will be thrown upon running the command with a migrations table already created.

## 3. Running migrations

To apply the migrations, run the following command:
```
php sentience.php migrations:apply
```

## 3. Rollback migrations

To rollback migrations, run the following command:
```
php sentience.php migrations:rollback
```

Each time the rollback command is executed, the last batch will be reverted.

## 4. Creating your own migrations

After running the `migrations:create` command, a new migration is created with an anonymous class with two methods, up() and down().

The Database class passed in as the parameters offers multiple options to programmatically build queries:
```
$database->select();
$database->insert();
$database->update();
$database->delete();
$database->createTable();
$database->alterTable();
$database->dropTable();
```

But if you require running raw queries, you can use the following methods:
```
$database->safe();
$database->unsafe();
```

The `safe()` methods offers prepared statements, while the `unsafe()` method just execute the query on the database, without any safeguarding.
