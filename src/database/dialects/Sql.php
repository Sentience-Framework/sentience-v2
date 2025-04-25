<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\containers\Alias;
use src\database\queries\containers\Join;
use src\database\queries\containers\OrderBy;
use src\database\queries\containers\Raw;
use src\database\queries\containers\Condition;
use src\database\queries\containers\ConditionGroup;
use src\database\queries\definitions\AddColumn;
use src\database\queries\definitions\AlterColumn;
use src\database\queries\definitions\Column;
use src\database\queries\definitions\DropColumn;
use src\database\queries\definitions\ForeignKeyConstraint;
use src\database\queries\definitions\RenameColumn;
use src\database\queries\definitions\UniqueConstraint;
use src\database\queries\enums\WhereOperator;
use src\exceptions\QueryException;

class Sql implements DialectInterface
{
    public const TABLE_OR_COLUMN_ESCAPE = '"';
    public const STRING_ESCAPE = "'";
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function addTable(string &$query, string|array|Alias|Raw $table, bool $allowAlias = false): void
    {
        $query .= ' ';

        if ($table instanceof Alias) {
            $query .= $this->escapeTableOrColumn($table->name, $allowAlias ? $table->alias : null);
            return;
        }

        if ($table instanceof Raw) {
            $query .= $table->expression;
            return;
        }

        $query .= $this->escapeTableOrColumn($table);
    }

    public function addJoins(string &$query, array $joins): void
    {
        if (count($joins) == 0) {
            return;
        }

        $query .= ' ';

        /**
         * @var Join|Raw[] $joins
         */
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
                $this->escapeTableOrColumn($join->joinTable, $join->joinTableAlias),
                $this->escapeTableOrColumn($join->joinTableAlias ?? $join->joinTable),
                $this->escapeTableOrColumn($join->joinTableColumn),
                $this->escapeTableOrColumn($join->onTable),
                $this->escapeTableOrColumn($join->onTableColumn),
            );
        }
    }

    public function addWhere(string &$query, array &$params, array $where): void
    {
        if (count($where) == 0) {
            return;
        }

        $query .= ' WHERE ';

        /**
         * @var Condition|ConditionGroup[] $where
         */
        foreach ($where as $index => $condition) {
            if ($condition instanceof Condition) {
                $this->addCondition($query, $params, $index, $condition);
            }

            if ($condition instanceof ConditionGroup) {
                $this->addConditionGroup($query, $params, $index, $condition);
            }
        }
    }

    protected function addCondition(string &$query, array &$params, int $index, Condition $condition): void
    {
        if ($index > 0) {
            $query .= sprintf(' %s ', $condition->chain->value);
        }

        if ($condition->type == WhereOperator::RAW) {
            $query .= sprintf('(%s)', $condition->expression);

            array_push($params, ...$condition->value);

            return;
        }

        if (is_null($condition->value)) {
            $comparator = ($condition->type == WhereOperator::EQUALS) ? 'IS NULL' : 'IS NOT NULL';

            $query .= sprintf(
                '(%s %s)',
                $this->escapeTableOrColumn($condition->expression),
                $comparator
            );

            return;
        }

        if (in_array($condition->type, [WhereOperator::BETWEEN, WhereOperator::NOT_BETWEEN])) {
            $query .= sprintf(
                '(%s %s ? AND ?)',
                $this->escapeTableOrColumn($condition->expression),
                $condition->type->value,
                $condition->value[0],
                $condition->value[1]
            );

            array_push($params, ...$condition->value);

            return;
        }

        if (is_array($condition->value)) {
            $query .= sprintf(
                '(%s %s (%s))',
                $this->escapeTableOrColumn($condition->expression),
                $condition->type->value,
                implode(', ', array_fill(0, count($condition->value), '?'))
            );

            array_push($params, ...$condition->value);

            return;
        }

        $query .= sprintf(
            '(%s %s ?)',
            $this->escapeTableOrColumn($condition->expression),
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

        /**
         * @var Condition|ConditionGroup[] $conditions
         */
        foreach ($conditions as $index => $condition) {
            if ($condition instanceof Condition) {
                $this->addCondition($query, $params, $index, $condition);
            }

            if ($condition instanceof ConditionGroup) {
                $this->addConditionGroup($query, $params, $index, $condition);
            }
        }

        $query .= ')';
    }

    public function addGroupBy(string &$query, array $groupBy): void
    {
        if (count($groupBy) == 0) {
            return;
        }

        $query .= ' ';

        $query .= sprintf(
            'GROUP BY %s',
            implode(
                ', ',
                array_map(
                    function (string|array|Raw $column): string {
                        return $this->escapeTableOrColumn($column);
                    },
                    $groupBy
                )
            )
        );
    }

    public function addHaving(string &$query, array &$params, ?string $having, array $values): void
    {
        if (is_null($having)) {
            return;
        }

        $query .= ' HAVING ' . $having;

        array_push($params, ...$values);
    }

    public function addOrderBy(string &$query, array $orderBy): void
    {
        if (count($orderBy) == 0) {
            return;
        }

        $query .= ' ';

        $query .= sprintf(
            'ORDER BY %s',
            implode(
                ', ',
                array_map(
                    function (OrderBy $orderBy): string {
                        return sprintf(
                            '%s %s',
                            $this->escapeTableOrColumn($orderBy->column),
                            $orderBy->direction->value
                        );
                    },
                    $orderBy
                )
            )
        );
    }

    public function addLimit(string &$query, ?int $limit): void
    {
        if (!$limit) {
            return;
        }

        $query .= ' LIMIT ' . $limit;
    }

    public function addOffset(string &$query, ?int $limit, ?int $offset): void
    {
        if (!$limit) {
            return;
        }

        if (!$offset) {
            return;
        }

        $query .= ' OFFSET ' . $offset;
    }

    public function addConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, array $insertValues, ?string $primaryKey): void
    {
        /**
         * The official SQL standard does not define a clear way to handle conflicts
         */

        return;
    }

    public function addReturning(string &$query, ?array $returning): void
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
                        return $this->escapeTableOrColumn($column);
                    },
                    $returning
                )
            );

        $query .= ' RETURNING ' . $columns;
    }

    public function stringifyColumnDefinition(Column $column): string
    {
        $stringifiedColumn = sprintf(
            '%s %s',
            $this->escapeTableOrColumn($column->name),
            $column->type
        );

        if ($column->notNull) {
            $stringifiedColumn .= ' NOT NULL';
        }

        if ($column->defaultValue && !$column->autoIncrement) {
            $defaultValue = preg_match('/^.*\(.*\)$/', $column->defaultValue)
                ? $column->defaultValue
                : $this->escapeString($column->defaultValue);

            $stringifiedColumn .= ' DEFAULT ' . $defaultValue;
        }

        return $stringifiedColumn;
    }

    public function stringifyUniqueConstraintDefinition(UniqueConstraint $uniqueConstraint): string
    {
        $stringifiedUniqueConstraint = sprintf(
            'UNIQUE (%s)',
            implode(
                ', ',
                array_map(
                    function (string $column): string {
                        return $this->escapeTableOrColumn($column);
                    },
                    $uniqueConstraint->columns
                )
            )
        );

        if ($uniqueConstraint->name) {
            return sprintf(
                'CONSTRAINT %s %s',
                $this->escapeTableOrColumn($uniqueConstraint->name),
                $stringifiedUniqueConstraint
            );
        }

        return $stringifiedUniqueConstraint;
    }

    public function stringifyForeignKeyConstraintDefinition(ForeignKeyConstraint $foreignKeyConstraint): string
    {
        $stringifiedForeignKeyConstraint = sprintf(
            'FOREIGN KEY (%s) REFERENCES %s(%s)',
            $foreignKeyConstraint->column,
            $foreignKeyConstraint->referenceTable,
            $foreignKeyConstraint->referenceColumn
        );

        if ($foreignKeyConstraint->name) {
            return sprintf(
                'CONSTRAINT %s %s',
                $this->escapeTableOrColumn($foreignKeyConstraint->name),
                $stringifiedForeignKeyConstraint
            );
        }

        return $stringifiedForeignKeyConstraint;
    }

    public function stringifyAlterTableAddColumn(AddColumn $addColumn): string
    {
        $stringifiedColumn = sprintf(
            'ADD COLUMN %s %s',
            $this->escapeTableOrColumn($addColumn->name),
            $addColumn->type
        );

        if ($addColumn->notNull) {
            $stringifiedColumn .= ' NOT NULL';
        }

        if ($addColumn->defaultValue && !$addColumn->autoIncrement) {
            $defaultValue = preg_match('/^.*\(.*\)$/', $addColumn->defaultValue)
                ? $addColumn->defaultValue
                : $this->escapeString($addColumn->defaultValue);

            $stringifiedColumn .= ' DEFAULT ' . $defaultValue;
        }

        return $stringifiedColumn;
    }

    public function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string
    {
        return sprintf(
            'ALTER COLUMN %s %s',
            $this->escapeTableOrColumn($alterColumn->column),
            $alterColumn->options
        );
    }

    public function stringifyAlterTableRenameColumn(RenameColumn $renameColumn): string
    {
        return sprintf(
            'RENAME COLUMN %s TO %s',
            $this->escapeTableOrColumn($renameColumn->oldName),
            $this->escapeTableOrColumn($renameColumn->newName)
        );
    }

    public function stringifyAlterTableDropColumn(DropColumn $dropColumn): string
    {
        return sprintf(
            'DROP COLUMN %s',
            $this->escapeTableOrColumn($dropColumn->column)
        );
    }

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        return [
            'bool' => 'INT',
            'int' => 'INT',
            'float' => 'FLOAT',
            'string' => 'TEXT',
            'DateTime' => 'DATETIME',
        ][$type];
    }

    public function escapeTableOrColumn(string|array|Raw $reference, ?string $alias = null): string
    {
        if ($reference instanceof Raw) {
            return $alias
                ? sprintf('%s %s', $reference->expression, $this->escape($alias, $this::TABLE_OR_COLUMN_ESCAPE))
                : $reference->expression;
        }

        $reference = is_array($reference)
            ? implode(
                '.',
                array_map(
                    function (string|Raw $reference): string {
                        return $this->escapeTableOrColumn($reference);
                    },
                    $reference
                )
            )
            : $this->escape($reference, $this::TABLE_OR_COLUMN_ESCAPE);

        if (!$alias) {
            return $reference;
        }

        return sprintf('%s %s', $reference, $this->escape($alias, $this::TABLE_OR_COLUMN_ESCAPE));
    }

    public function escapeString(string $string): string
    {
        return $this->escape($string, $this::STRING_ESCAPE);
    }

    protected function escape(string $string, string $character): string
    {
        $escapedString = escape_chars(
            $string,
            ['\\', $character],
            '$0$0'
        );

        return $character . $escapedString . $character;
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
        return ($value == 1) ? true : false;
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

    public function toRawQuery(string $query, array $params): string
    {
        if (count($params) == 0) {
            return $query;
        }

        $params = array_map(
            function (mixed $param): mixed {
                return $this->castToQuery($param);
            },
            $params
        );

        $stringEscape = $this::STRING_ESCAPE;

        $regex = sprintf(
            '/(?<!\\\)(\?)(?=(?:[^%s]|%s[^%s]*%s)*$)/',
            $stringEscape,
            $stringEscape,
            $stringEscape,
            $stringEscape,
        );

        $index = 0;

        return preg_replace_callback(
            $regex,
            function () use ($params, &$index): mixed {
                if (!key_exists($index, $params)) {
                    throw new QueryException('placeholder and value count do not match');
                }

                $param = $params[$index];

                $index++;

                return $param;
            },
            $query
        );
    }
}
