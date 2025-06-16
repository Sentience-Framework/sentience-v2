<?php

namespace src\database\dialects;

use src\database\queries\objects\AddForeignKeyConstraint;
use src\database\queries\objects\AddUniqueConstraint;
use src\database\queries\objects\AlterColumn;
use src\database\queries\objects\DropConstraint;
use src\database\queries\objects\Raw;
use src\exceptions\QueryException;

class Sqlite extends Sql implements DialectInterface
{
    public function addOnConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, ?string $primaryKey, array $insertValues): void
    {
        if (is_null($conflict)) {
            return;
        }

        if (is_string($conflict)) {
            throw new QueryException('SQLite does not support ON CONFLICT ON CONSTRAINT, please use an array of columns');
        }

        $expression = sprintf(
            '(%s)',
            implode(
                ', ',
                array_map(
                    function (string $column): string {
                        return $this->escapeIdentifier($column);
                    },
                    $conflict
                )
            )
        );

        if (is_null($conflictUpdates)) {
            $query .= sprintf(' ON CONFLICT %s DO NOTHING', $expression);

            return;
        }

        $updates = !empty($conflictUpdates) ? $conflictUpdates : $insertValues;

        $query .= sprintf(
            ' ON CONFLICT %s DO UPDATE SET %s',
            $expression,
            implode(
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
                    $updates,
                    array_keys($updates)
                )
            )
        );
    }

    public function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string
    {
        throw new QueryException('SQLite does not support altering columns');
    }

    protected function stringifyAlterTableAddUniqueConstraint(AddUniqueConstraint $addUniqueConstraint): string
    {
        throw new QueryException('SQLite does not support adding constraints by altering the table');
    }

    protected function stringifyAlterTableAddForeignKeyConstraint(AddForeignKeyConstraint $addForeignKeyConstraint): string
    {
        throw new QueryException('SQLite does not support adding constraints by altering the table');
    }

    protected function stringifyAlterTableDropConstraint(DropConstraint $dropConstraint): string
    {
        throw new QueryException('SQLite does not support dropping constraints by altering the table');
    }

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        return match ($type) {
            'bool' => 'BOOLEAN',
            'int' => 'INTEGER',
            'float' => 'REAL',
            'string' => 'TEXT',
            'DateTime' => 'DATETIME',
            default => 'TEXT'
        };
    }
}
