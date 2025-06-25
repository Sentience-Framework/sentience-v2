<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\enums\WhereType;
use src\database\queries\objects\AddColumn;
use src\database\queries\objects\AddForeignKeyConstraint;
use src\database\queries\objects\AddPrimaryKeys;
use src\database\queries\objects\AddUniqueConstraint;
use src\database\queries\objects\Alias;
use src\database\queries\objects\AlterColumn;
use src\database\queries\objects\Column;
use src\database\queries\objects\Condition;
use src\database\queries\objects\ConditionGroup;
use src\database\queries\objects\DropColumn;
use src\database\queries\objects\DropConstraint;
use src\database\queries\objects\ForeignKeyConstraint;
use src\database\queries\objects\OrderBy;
use src\database\queries\objects\QueryWithParams;
use src\database\queries\objects\Raw;
use src\database\queries\objects\RenameColumn;
use src\database\queries\objects\UniqueConstraint;

class Sql implements DialectInterface
{
    public const IDENTIFIER_ESCAPE = '"';
    public const STRING_ESCAPE = "'";
    public const ANSI_ESCAPE = true;
    public const DATETIME_FORMAT = 'Y-m-d H:i:s.u';
    public const REGEX_FUNCTION = 'REGEXP';
    public const NOT_REGEX_FUNCTION = 'NOT REGEXP';

    public function select(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'SELECT';

        if ($config['distinct']) {
            $query .= ' DISTINCT';
        }

        $query .= ' ';
        $query .= count($config['columns']) > 0
            ? implode(
                ', ',
                array_map(
                    function (string|array|Alias|Raw $column): string {
                        if (is_array($column)) {
                            return $this->escapeIdentifier($column);
                        }

                        if ($column instanceof Alias) {
                            return $this->escapeIdentifierWithAlias($column->name, $column->alias);
                        }

                        if ($column instanceof Raw) {
                            return $column->expression;
                        }

                        return $this->escapeIdentifier($column);
                    },
                    $config['columns']
                )
            )
            : '*';

        $query .= ' FROM';

        $this->addTable($query, $config['table']);
        $this->addJoins($query, $config['joins']);
        $this->addWhere($query, $params, $config['where']);
        $this->addGroupBy($query, $config['groupBy']);
        $this->addHaving($query, $params, $config['having']);
        $this->addOrderBy($query, $config['orderBy']);
        $this->addLimit($query, $config['limit']);
        $this->addOffset($query, $config['limit'], $config['offset']);

        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    public function insert(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'INSERT INTO';

        $this->addTable($query, $config['table']);

        $query .= sprintf(
            ' (%s)',
            implode(
                ', ',
                array_map(
                    function (string|array|Alias|Raw $column): string {
                        if ($column instanceof Raw) {
                            return $column->expression;
                        }

                        if ($column instanceof Alias) {
                            return $this->escapeIdentifier($column->name);
                        }

                        return $this->escapeIdentifier($column);
                    },
                    array_keys($config['values'])
                )
            )
        );

        $query .= sprintf(
            ' VALUES (%s)',
            implode(
                ', ',
                array_map(
                    function (mixed $value) use (&$params): string {
                        if ($value instanceof Raw) {
                            return $value->expression;
                        }

                        $params[] = $value;

                        return '?';
                    },
                    $config['values']
                )
            )
        );

        $this->addOnConflict(
            $query,
            $params,
            $config['onConflict']['conflict'],
            $config['onConflict']['updates'],
            $config['onConflict']['primaryKey'],
            $config['values']
        );
        $this->addReturning($query, $config['returning']);

        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    public function update(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'UPDATE';

        $this->addTable($query, $config['table']);

        $query .= ' SET ';
        $query .= implode(
            ', ',
            array_map(
                function (mixed $value, string $key) use (&$params): string {
                    if ($value instanceof Raw) {
                        return sprintf(
                            '%s = %s',
                            $this->escapeIdentifier($key),
                            $value->expression
                        );
                    }

                    $params[] = $value;

                    return sprintf('%s = ?', $this->escapeIdentifier($key));
                },
                $config['values'],
                array_keys($config['values'])
            )
        );

        $this->addWhere($query, $params, $config['where']);
        $this->addLimit($query, $config['limit']);
        $this->addReturning($query, $config['returning']);

        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    public function delete(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'DELETE FROM';

        $this->addTable($query, $config['table']);
        $this->addWhere($query, $params, $config['where']);
        $this->addReturning($query, $config['returning']);

        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    public function createTable(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'CREATE TABLE';

        if ($config['ifNotExists']) {
            $query .= ' IF NOT EXISTS';
        }

        $this->addTable($query, $config['table']);

        $definitions = [];

        foreach ($config['columns'] as $column) {
            $definitions[] = $this->stringifyColumnDefinition($column);
        }

        $definitions[] = sprintf(
            'PRIMARY KEY (%s)',
            implode(
                ', ',
                array_map(
                    function (string|Raw $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $config['primaryKeys']
                )
            )
        );

        foreach ($config['constraints']['unique'] as $uniqueConstraint) {
            $definitions[] = $this->stringifyUniqueConstraintDefinition($uniqueConstraint);
        }

        foreach ($config['constraints']['foreignKey'] as $foreignKeyConstraint) {
            $definitions[] = $this->stringifyForeignKeyConstraintDefinition($foreignKeyConstraint);
        }

        $query .= sprintf(' (%s)', implode(', ', $definitions));
        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    public function alterTable(array $config): QueryWithParams
    {
        $alters = [];

        foreach ($config['alters'] as $alter) {
            if ($alter instanceof AddColumn) {
                $alters[] = $this->stringifyAlterTableAddColumn($alter);

                continue;
            }

            if ($alter instanceof AlterColumn) {
                $alters[] = $this->stringifyAlterTableAlterColumn($alter);

                continue;
            }

            if ($alter instanceof RenameColumn) {
                $alters[] = $this->stringifyAlterTableRenameColumn($alter);

                continue;
            }

            if ($alter instanceof DropColumn) {
                $alters[] = $this->stringifyAlterTableDropColumn($alter);

                continue;
            }

            if ($alter instanceof AddPrimaryKeys) {
                $alters[] = $this->stringifyAlterTableAddPrimaryKeys($alter);

                continue;
            }

            if ($alter instanceof AddUniqueConstraint) {
                $alters[] = $this->stringifyAlterTableAddUniqueConstraint($alter);

                continue;
            }

            if ($alter instanceof AddForeignKeyConstraint) {
                $alters[] = $this->stringifyAlterTableAddForeignKeyConstraint($alter);

                continue;
            }

            if ($alter instanceof DropConstraint) {
                $alters[] = $this->stringifyAlterTableDropConstraint($alter);

                continue;
            }
        }

        $queries = array_map(
            function (string $alter) use ($config): string {
                $query = 'ALTER TABLE';

                $this->addTable($query, $config['table']);

                $query .= ' ';
                $query .= $alter;
                $query .= ';';

                return $query;
            },
            $alters
        );

        $query = implode(' ', $queries);

        return new QueryWithParams($query);
    }

    public function dropTable(array $config): QueryWithParams
    {
        $query = '';
        $params = [];

        $query .= 'DROP TABLE';

        if ($config['ifExists']) {
            $query .= ' IF EXISTS';
        }

        $this->addTable($query, $config['table']);

        $query .= ';';

        return new QueryWithParams($query, $params);
    }

    protected function addTable(string &$query, string|array|Alias|Raw $table): void
    {
        $query .= ' ';

        if ($table instanceof Alias) {
            $query .= $this->escapeIdentifierWithAlias($table->name, $table->alias);

            return;
        }

        if ($table instanceof Raw) {
            $query .= $table->expression;

            return;
        }

        $query .= $this->escapeIdentifier($table);
    }

    protected function addJoins(string &$query, array $joins): void
    {
        if (count($joins) == 0) {
            return;
        }

        $query .= ' ';

        foreach ($joins as $index => $join) {
            if ($index > 0) {
                $query .= ' ';
            }

            if ($join instanceof Raw) {
                $query .= $join->expression;

                continue;
            }

            $query .= sprintf(
                '%s %s ON %s.%s = %s.%s',
                $join->type->value,
                $this->escapeIdentifierWithAlias($join->joinTable, $join->joinTableAlias),
                $this->escapeIdentifier($join->joinTableAlias ?? $join->joinTable),
                $this->escapeIdentifier($join->joinTableColumn),
                $this->escapeIdentifier($join->onTable),
                $this->escapeIdentifier($join->onTableColumn)
            );
        }
    }

    protected function addWhere(string &$query, array &$params, array $where): void
    {
        if (count($where) == 0) {
            return;
        }

        $query .= ' WHERE ';

        foreach ($where as $index => $condition) {
            $condition instanceof Condition
                ? $this->addCondition($query, $params, $index, $condition)
                : $this->addConditionGroup($query, $params, $index, $condition);
        }
    }

    protected function addCondition(string &$query, array &$params, int $index, Condition $condition): void
    {
        if ($index > 0) {
            $query .= sprintf(' %s ', $condition->chain->value);
        }

        if ($condition->type == WhereType::RAW) {
            $query .= sprintf('(%s)', $condition->expression);

            array_push($params, ...$condition->value);

            return;
        }

        if (is_null($condition->value)) {
            $query .= sprintf(
                '(%s %s)',
                $this->escapeIdentifier($condition->expression),
                $condition->type == WhereType::EQUALS ? 'IS NULL' : 'IS NOT NULL'
            );

            return;
        }

        if (in_array($condition->type, [WhereType::BETWEEN, WhereType::NOT_BETWEEN])) {
            $query .= sprintf(
                '(%s %s ? AND ?)',
                $this->escapeIdentifier($condition->expression),
                $condition->type->value,
                $condition->value[0],
                $condition->value[1]
            );

            array_push($params, ...$condition->value);

            return;
        }

        if (is_array($condition->value)) {
            if (count($condition->value) == 0) {
                $query .= $condition->type == WhereType::IN ? '(1 <> 1)' : '(1 = 1)';

                return;
            }

            $query .= sprintf(
                '(%s %s (%s))',
                $this->escapeIdentifier($condition->expression),
                $condition->type->value,
                implode(', ', array_fill(0, count($condition->value), '?'))
            );

            array_push($params, ...$condition->value);

            return;
        }

        if (in_array($condition->type, [WhereType::REGEX, WhereType::NOT_REGEX])) {
            $query .= sprintf(
                '(%s %s ?)',
                $this->escapeIdentifier($condition->expression),
                $condition->type == WhereType::REGEX ? $this::REGEX_FUNCTION : $this::NOT_REGEX_FUNCTION
            );

            array_push($params, $condition->value);

            return;
        }

        $query .= sprintf(
            '(%s %s ?)',
            $this->escapeIdentifier($condition->expression),
            $condition->type->value
        );

        array_push($params, $condition->value);
    }

    protected function addConditionGroup(string &$query, array &$params, int $index, ConditionGroup $group): void
    {
        if ($index > 0) {
            $query .= sprintf(' %s ', $group->chain->value);
        }

        $conditions = $group->getConditions();

        $query .= '(';

        foreach ($conditions as $index => $condition) {
            $condition instanceof Condition
                ? $this->addCondition($query, $params, $index, $condition)
                : $this->addConditionGroup($query, $params, $index, $condition);
        }

        $query .= ')';
    }

    protected function addGroupBy(string &$query, array $groupBy): void
    {
        if (count($groupBy) == 0) {
            return;
        }

        $query .= sprintf(
            ' GROUP BY %s',
            implode(
                ', ',
                array_map(
                    function (string|array|Raw $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $groupBy
                )
            )
        );
    }

    protected function addHaving(string &$query, array &$params, ?QueryWithParams $having): void
    {
        if (is_null($having)) {
            return;
        }

        $query .= ' HAVING ' . $having->expression;

        array_push($params, ...$having->params);
    }

    protected function addOrderBy(string &$query, array $orderBy): void
    {
        if (count($orderBy) == 0) {
            return;
        }

        $query .= sprintf(
            ' ORDER BY %s',
            implode(
                ', ',
                array_map(
                    function (OrderBy $orderBy): string {
                        return sprintf(
                            '%s %s',
                            $this->escapeIdentifier($orderBy->column),
                            $orderBy->direction->value
                        );
                    },
                    $orderBy
                )
            )
        );
    }

    protected function addLimit(string &$query, ?int $limit): void
    {
        if (!$limit) {
            return;
        }

        $query .= ' LIMIT ' . $limit;
    }

    protected function addOffset(string &$query, ?int $limit, ?int $offset): void
    {
        if (!$limit) {
            return;
        }

        if (!$offset) {
            return;
        }

        $query .= ' OFFSET ' . $offset;
    }

    protected function addOnConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, ?string $primaryKey, array $insertValues): void
    {
        /**
         * The official SQL standard does not define a clear way to handle conflicts
         */

        return;
    }

    protected function addReturning(string &$query, ?array $returning): void
    {
        if (is_null($returning)) {
            return;
        }

        $columns = empty($returning)
            ? '*'
            : implode(
                ', ',
                array_map(
                    function (string $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $returning
                )
            );

        $query .= ' RETURNING ' . $columns;
    }

    protected function stringifyColumnDefinition(Column $column): string
    {
        $stringifiedColumn = sprintf(
            '%s %s',
            $this->escapeIdentifier($column->name),
            $column->type
        );

        if ($column->notNull) {
            $stringifiedColumn .= ' NOT NULL';
        }

        if (!is_null($column->defaultValue) && !$column->autoIncrement) {
            $defaultValue = preg_match('/^.*\(.*\)$/', $column->defaultValue)
                ? $column->defaultValue
                : $this->escapeString($column->defaultValue);

            $stringifiedColumn .= ' DEFAULT ' . $defaultValue;
        }

        return $stringifiedColumn;
    }

    protected function stringifyUniqueConstraintDefinition(UniqueConstraint $uniqueConstraint): string
    {
        $stringifiedUniqueConstraint = sprintf(
            'UNIQUE (%s)',
            implode(
                ', ',
                array_map(
                    function (string $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $uniqueConstraint->columns
                )
            )
        );

        if ($uniqueConstraint->name) {
            return sprintf(
                'CONSTRAINT %s %s',
                $this->escapeIdentifier($uniqueConstraint->name),
                $stringifiedUniqueConstraint
            );
        }

        return $stringifiedUniqueConstraint;
    }

    protected function stringifyForeignKeyConstraintDefinition(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        $stringifiedForeignKeyConstraint = sprintf(
            'FOREIGN KEY (%s) REFERENCES %s (%s)',
            $foreignKeyConstraint->column,
            $foreignKeyConstraint->referenceTable,
            $foreignKeyConstraint->referenceColumn
        );

        if ($foreignKeyConstraint->name) {
            return sprintf(
                'CONSTRAINT %s %s',
                $this->escapeIdentifier($foreignKeyConstraint->name),
                $stringifiedForeignKeyConstraint
            );
        }

        return $stringifiedForeignKeyConstraint;
    }

    protected function stringifyAlterTableAddColumn(AddColumn $addColumn): string
    {
        return sprintf(
            'ADD COLUMN %s',
            $this->stringifyColumnDefinition($addColumn)
        );
    }

    protected function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string
    {
        return sprintf(
            'ALTER COLUMN %s %s',
            $this->escapeIdentifier($alterColumn->column),
            $alterColumn->options
        );
    }

    protected function stringifyAlterTableRenameColumn(RenameColumn $renameColumn): string
    {
        return sprintf(
            'RENAME COLUMN %s TO %s',
            $this->escapeIdentifier($renameColumn->oldName),
            $this->escapeIdentifier($renameColumn->newName)
        );
    }

    protected function stringifyAlterTableDropColumn(DropColumn $dropColumn): string
    {
        return sprintf(
            'DROP COLUMN %s',
            $this->escapeIdentifier($dropColumn->column)
        );
    }

    protected function stringifyAlterTableAddPrimaryKeys(AddPrimaryKeys $addPrimaryKeys): string
    {
        return sprintf(
            'ADD PRIMARY KEY (%s)',
            implode(
                ', ',
                array_map(
                    function (string|array|Raw $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $addPrimaryKeys->columns
                )
            )
        );
    }

    protected function stringifyAlterTableAddUniqueConstraint(AddUniqueConstraint $addUniqueConstraint): string
    {
        return sprintf(
            'ADD %s',
            $this->stringifyUniqueConstraintDefinition($addUniqueConstraint)
        );
    }

    protected function stringifyAlterTableAddForeignKeyConstraint(AddForeignKeyConstraint $addForeignKeyConstraint): string
    {
        return sprintf(
            'ADD %s',
            $this->stringifyForeignKeyConstraintDefinition($addForeignKeyConstraint)
        );
    }

    protected function stringifyAlterTableDropConstraint(DropConstraint $dropConstraint): string
    {
        return sprintf(
            'DROP CONSTRAINT %s',
            $this->escapeIdentifier($dropConstraint->constraint)
        );
    }

    protected function escapeIdentifierWithAlias(string|array|Raw $identifier, ?string $alias): string
    {
        $escapedIdentifier = $this->escapeIdentifier($identifier);

        if (!$alias) {
            return $escapedIdentifier;
        }

        return sprintf('%s AS %s', $escapedIdentifier, $this->escapeIdentifier($alias));
    }

    public function escapeIdentifier(string|array|Raw $identifier): string
    {
        if ($identifier instanceof Raw) {
            return $identifier->expression;
        }

        return is_array($identifier)
            ? implode(
                '.',
                array_map(
                    function (string|Raw $identifier): string {
                        return $this->escapeIdentifier($identifier);
                    },
                    $identifier
                )
            )
            : $this->escape($identifier, $this::IDENTIFIER_ESCAPE);
    }

    public function escapeString(string $string): string
    {
        return $this->escape($string, $this::STRING_ESCAPE);
    }

    protected function escape(string $string, string $char): string
    {
        $escapedString = $this::ANSI_ESCAPE
            ? escape_chars($string, [$char], '$0$0', '/%s/')
            : escape_chars($string, ['\\', $char]);

        return $char . $escapedString . $char;
    }

    public function castToDriver(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $this->castBool($value);
        }

        if ($value instanceof DateTime) {
            return $this->castDateTime($value);
        }

        return $value;
    }

    public function castToQuery(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->escapeString($value);
        }

        if (is_bool($value)) {
            $bool = $this->castBool($value);

            return is_string($bool)
                ? $this->escapeString($bool)
                : $bool;
        }

        if (is_null($value)) {
            return 'NULL';
        }

        if ($value instanceof DateTime) {
            return $this->escapeString($this->castDateTime($value));
        }

        return $value;
    }

    public function castBool(bool $bool): mixed
    {
        return $bool ? 1 : 0;
    }

    public function castDateTime(DateTime $dateTime): mixed
    {
        return $dateTime->format($this::DATETIME_FORMAT);
    }

    public function parseBool(mixed $value): bool
    {
        return $value == 1 ? true : false;
    }

    public function parseDateTime(?string $dateTimeString): ?DateTime
    {
        if (!$dateTimeString) {
            return null;
        }

        $dateTime = DateTime::createFromFormat($this::DATETIME_FORMAT, $dateTimeString);

        if ($dateTime) {
            return $dateTime;
        }

        $timestamp = strtotime($dateTimeString);

        if (!$timestamp) {
            return null;
        }

        $dateTime = new DateTime();

        return $dateTime->setTimestamp($timestamp);
    }

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        return match ($type) {
            'bool' => 'INT',
            'int' => 'INT',
            'float' => 'FLOAT',
            'string' => 'TEXT',
            'DateTime' => 'DATETIME',
            default => 'TEXT'
        };
    }
}
