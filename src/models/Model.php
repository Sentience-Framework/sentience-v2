<?php

namespace src\models;

use ReflectionProperty;
use src\database\Database;
use src\database\dialects\DialectFactory;
use src\database\dialects\DialectInterface;
use src\database\queries\Insert;
use src\exceptions\ModelException;
use src\exceptions\RelationException;
use src\models\relations\BelongsTo;
use src\models\relations\HasMany;
use src\models\relations\HasOne;
use src\models\relations\ManyToMany;
use src\utils\Reflector;

abstract class Model
{
    protected Database $database;
    protected DialectInterface $dialect;
    protected string $table;
    protected string $primaryKey;
    protected bool $primaryKeyAutoIncrement;
    protected array $columns = [];
    protected array $unique = [];
    protected array $relations = [];

    public function __construct(Database $database, ?object $record = null)
    {
        $this->database = $database;
        $this->dialect = DialectFactory::fromDatabase($database);

        if ($record) {
            $this->fromRecord($record);
        }
    }

    public function fromRecord(object $record): void
    {
        foreach ($this->columns as $property => $column) {
            if (!property_exists($record, $column)) {
                continue;
            }

            if (!Reflector::hasSingularType($this, $property)) {
                throw new ModelException('empty or union types are not allowed as model properties');
            }

            $reflectionProperty = new ReflectionProperty($this, $property);
            $reflectionType = $reflectionProperty->getType();

            $propertyType = $reflectionType->getName();
            $propertyAllowsNull = $reflectionType->allowsNull();

            $value = $record->{$column};

            if (is_null($value)) {
                if (!$propertyAllowsNull) {
                    throw new ModelException(
                        'column %s contains null value, while property %s does not allow null',
                        $column,
                        $property
                    );
                }

                $this->{$property} = null;

                continue;
            }

            if ($propertyType == 'bool') {
                $this->{$property} = $this->dialect->parseBool($value);

                continue;
            }

            if ($propertyType == 'DateTime') {
                $this->{$property} = $this->dialect->parseDateTime($value);

                continue;
            }

            $this->{$property} = $value;
        }
    }

    public function select(mixed $primaryKeyValue = null, array $relations = [], ?callable $modifyQuery = null): static
    {
        $query = $this->database->select()
            ->table($this->table)
            ->columns(array_values($this->columns))
            ->whereEquals(
                $this->getColumnByProperty($this->primaryKey),
                $primaryKeyValue ?? $this->{$this->primaryKey}
            );

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $results = $query->execute();

        $record = $results->fetch();

        if (!$record) {
            throw new ModelException('unable to find record for model');
        }

        $this->fromRecord($record);

        foreach ($relations as $property) {
            $this->selectRelation($property);
        }

        $this->onSelect();

        return $this;
    }

    public function selectRelation(string $property, ?callable $modifyQuery = null): static
    {
        if (!array_key_exists($property, $this->relations)) {
            throw new RelationException('relation %s not defined in model', $property);
        }

        $this->{$property} = $this->relations[$property]->retrieve($this->database, $this, $modifyQuery);

        return $this;
    }

    public function insert(?callable $modifyQuery = null): static
    {
        $values = [];

        foreach ($this->columns as $property => $column) {
            if (!Reflector::isPropertyInitialized($this, $property)) {
                continue;
            }

            $values[$column] = $this->{$property};
        }

        $query = $this->database->insert()
            ->table($this->table)
            ->values($values)
            ->returning(array_values($this->columns));

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $results = $query->execute();

        $lastInsertId = $results->lastInsertId();

        if ($lastInsertId) {
            $this->{$this->primaryKey} = $lastInsertId;
        }

        $insertedRecord = $results->fetch();

        if ($insertedRecord) {
            $this->fromRecord($insertedRecord);
        }

        $this->onInsert();

        return $this;
    }

    public function upsert(bool $update = true, ?callable $modifyQuery = null): static
    {
        $this->insert(function (Insert $query) use ($update, $modifyQuery): Insert {
            $columns = array_map(
                function (string $property): string {
                    return $this->getColumnByProperty($property);
                },
                !empty($this->unique) ? $this->unique : [$this->primaryKey]
            );

            $update
                ? $query->onConflictUpdate($columns, [], $this->getPrimaryKeyColumn())
                : $query->onConflictIgnore($columns, $this->getPrimaryKeyColumn());

            if (!$modifyQuery) {
                return $query;
            }

            return $modifyQuery($query);
        });

        return $this;
    }

    public function update(?callable $modifyQuery = null): static
    {
        $values = [];

        foreach ($this->columns as $property => $column) {
            if (!Reflector::isPropertyInitialized($this, $property)) {
                continue;
            }

            $values[$column] = $this->{$property};
        }

        $primaryKey = $this->primaryKey;

        $query = $this->database->update()
            ->table($this->table)
            ->values($values)
            ->whereEquals(
                $this->getColumnByProperty($primaryKey),
                $this->{$primaryKey}
            )
            ->returning(array_values($this->columns));

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $results = $query->execute();

        $updatedRecord = $results->fetch();

        if ($updatedRecord) {
            $this->fromRecord($updatedRecord);
        }

        $this->onUpdate();

        return $this;
    }

    public function delete(?callable $modifyQuery = null): static
    {
        $query = $this->database->delete()
            ->table($this->table)
            ->whereEquals(
                $this->getColumnByProperty($this->primaryKey),
                $this->{$this->primaryKey}
            )
            ->returning(array_values($this->columns));

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $results = $query->execute();

        $deletedRecord = $results->fetch();

        if ($deletedRecord) {
            $this->fromRecord($deletedRecord);
        }

        $this->onDelete();

        return $this;
    }

    public function createTable(bool $ifNotExists = false, ?callable $modifyQuery = null): string
    {
        if (count($this->columns) == 0) {
            return '';
        }

        $query = $this->database->createTable()
            ->table($this->table)
            ->primaryKeys($this->getColumnByProperty($this->primaryKey));

        if ($ifNotExists) {
            $query->ifNotExists();
        }

        foreach ($this->columns as $property => $column) {
            if (!Reflector::hasSingularType($this, $property)) {
                throw new ModelException('empty or union types are not allowed as model properties');
            }

            $reflectionProperty = new ReflectionProperty($this, $property);
            $reflectionType = $reflectionProperty->getType();

            $propertyType = $reflectionType->getName();
            $propertyAllowsNull = $reflectionType->allowsNull();
            $propertyHasDefaultValue = $reflectionProperty->hasDefaultValue();
            $propertyDefaultValue = $reflectionProperty->getDefaultValue();
            $propertyIsPrimaryKey = $property == $this->primaryKey;

            $columnType = $this->dialect->phpTypeToColumnType(
                $propertyType,
                $propertyIsPrimaryKey ? $this->primaryKeyAutoIncrement : false,
                $propertyIsPrimaryKey,
                in_array($property, $this->unique)
            );

            $query->column(
                $column,
                $columnType,
                !$propertyAllowsNull,
                $propertyHasDefaultValue ? $propertyDefaultValue : null,
                $propertyIsPrimaryKey ? $this->primaryKeyAutoIncrement : false
            );
        }

        if (!empty($this->unique)) {
            $query->uniqueConstraint(
                array_map(
                    function (string $property): string {
                        return $this->getColumnByProperty($property);
                    },
                    $this->unique
                )
            );
        }

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $query->execute();

        $this->onCreate();

        return $query->toRawQuery();
    }

    public function alterTable(?callable $modifyQuery = null): string
    {
        $columnsInModel = array_flip($this->columns);

        $columnsInDatabase = $this->database->select()
            ->table($this->table)
            ->limit(0)
            ->execute()
            ->getColumns();

        $columnsToAdd = [];
        $columnsToDrop = [];

        foreach ($columnsInModel as $column => $property) {
            if (in_array($column, $columnsInDatabase)) {
                continue;
            }

            $columnsToAdd[] = $column;
        }

        foreach ($columnsInDatabase as $column) {
            if (array_key_exists($column, $columnsInModel)) {
                continue;
            }

            $columnsToDrop[] = $column;
        }

        if ((count($columnsToAdd) + count($columnsToDrop)) == 0) {
            return '';
        }

        $query = $this->database->alterTable()
            ->table($this->table);

        foreach ($columnsToAdd as $column) {
            $property = $columnsInModel[$column];

            if (!Reflector::hasSingularType($this, $property)) {
                throw new ModelException('empty or union types are not allowed as model properties');
            }

            $reflectionProperty = new ReflectionProperty($this, $property);
            $reflectionType = $reflectionProperty->getType();

            $propertyType = $reflectionType->getName();
            $propertyAllowsNull = $reflectionType->allowsNull();
            $propertyHasDefaultValue = $reflectionProperty->hasDefaultValue();
            $propertyDefaultValue = $reflectionProperty->getDefaultValue();
            $propertyIsPrimaryKey = $property == $this->primaryKey;

            $columnType = $this->dialect->phpTypeToColumnType(
                $propertyType,
                $propertyIsPrimaryKey ? $this->primaryKeyAutoIncrement : false,
                $propertyIsPrimaryKey,
                in_array($property, $this->unique)
            );

            $query->addColumn(
                $column,
                $columnType,
                !$propertyAllowsNull,
                $propertyHasDefaultValue ? $propertyDefaultValue : null,
                $propertyIsPrimaryKey ? $this->primaryKeyAutoIncrement : false
            );

            if ($propertyIsPrimaryKey) {
                $query->addPrimaryKeys($column);
            }
        }

        foreach ($columnsToDrop as $column) {
            $query->dropColumn($column);
        }

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $query->execute();

        $this->onAlter();

        return $query->toRawQuery();
    }

    public function dropTable(bool $ifExists = false, ?callable $modifyQuery = null): string
    {
        $query = $this->database->dropTable()
            ->table($this->table);

        if ($ifExists) {
            $query->ifExists();
        }

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        $query->execute();

        $this->onDrop();

        return $query->toRawQuery();
    }

    protected function hasOne(string $property, string $relationModel, string $mToRJoin, ?callable $modifyDefaultQuery = null): void
    {
        $this->relations[$property] = new HasOne(
            $relationModel,
            $mToRJoin,
            $modifyDefaultQuery
        );
    }

    protected function belongsTo(string $property, string $relationModel, string $mToRJoin, ?callable $modifyDefaultQuery = null): void
    {
        $this->relations[$property] = new BelongsTo(
            $relationModel,
            $mToRJoin,
            $modifyDefaultQuery
        );
    }

    protected function hasMany(string $property, string $relationModel, string $mToRJoin, ?callable $modifyDefaultQuery = null): void
    {
        $this->relations[$property] = new HasMany(
            $relationModel,
            $mToRJoin,
            $modifyDefaultQuery
        );
    }

    protected function manyToMany(string $property, string $relationModel, string $junctionTable, string $mToRJoin, ?callable $modifyDefaultQuery = null): void
    {
        $this->relations[$property] = new ManyToMany(
            $relationModel,
            $junctionTable,
            $mToRJoin,
            $modifyDefaultQuery
        );
    }

    protected function onSelect(): void
    {
        return;
    }

    protected function onInsert(): void
    {
        return;
    }

    protected function onUpdate(): void
    {
        return;
    }

    protected function onDelete(): void
    {
        return;
    }

    protected function onCreate(): void
    {
        return;
    }

    protected function onAlter(): void
    {
        return;
    }

    protected function onDrop(): void
    {
        return;
    }

    public static function getPropertyByColumn(string $column): string
    {
        $columns = Reflector::getDefaultValue(static::class, 'columns');

        $property = array_flip($columns)[$column] ?? null;

        if (!$property) {
            throw new ModelException('no property for %s set in model', $property);
        }

        return $property;
    }

    public static function getColumnByProperty(string $property): string
    {
        $columns = Reflector::getDefaultValue(static::class, 'columns');

        $column = $columns[$property] ?? null;

        if (!$column) {
            throw new ModelException('no column for %s set in model', $property);
        }

        return $column;
    }

    public static function getTable(): string
    {
        $table = Reflector::getDefaultValue(static::class, 'table');

        if (!$table) {
            throw new ModelException('no table set in model');
        }

        return $table;
    }

    public static function getColumns(): array
    {
        $columns = Reflector::getDefaultValue(static::class, 'columns');

        if (!$columns) {
            throw new ModelException('no columns set in model');
        }

        return array_values($columns);
    }

    public static function getPrimaryKeyProperty(): string
    {
        $primaryKeyProperty = Reflector::getDefaultValue(static::class, 'primaryKey');

        if (!$primaryKeyProperty) {
            throw new ModelException('no primary key set in model');
        }

        return $primaryKeyProperty;
    }

    public static function getPrimaryKeyColumn(): string
    {
        $primaryKeyProperty = static::getPrimaryKeyProperty();

        return static::getColumnByProperty($primaryKeyProperty);
    }
}
