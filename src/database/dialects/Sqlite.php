<?php

namespace src\database\dialects;

use src\database\queries\containers\Raw;
use src\database\queries\definitions\AlterColumn;
use src\exceptions\QueryException;

class Sqlite extends Sql implements DialectInterface
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public function addConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, ?string $primaryKey, array $insertValues): void
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
                        return $this->escapeTableOrColumn($column);
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
                                $this->escapeTableOrColumn($key),
                                $value->expression
                            );
                        }

                        $params[] = $value;

                        return sprintf('%s = ?', $this->escapeTableOrColumn($key));
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

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        return [
            'bool' => 'BOOLEAN',
            'int' => 'INTEGER',
            'float' => 'REAL',
            'string' => 'TEXT',
            'DateTime' => 'DATETIME',
        ][$type];
    }
}
