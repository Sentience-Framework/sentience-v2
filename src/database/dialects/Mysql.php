<?php

namespace src\database\dialects;

use src\database\queries\containers\Raw;
use src\database\queries\definitions\AddColumn;
use src\database\queries\definitions\AlterColumn;
use src\database\queries\definitions\Column;
use src\database\queries\Query;

class Mysql extends Sql implements DialectInterface
{
    public const TABLE_OR_COLUMN_ESCAPE = '`';

    public function addConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, array $values, ?string $primaryKey): void
    {
        if (is_null($conflict)) {
            return;
        }

        if (is_null($conflictUpdates) && !$primaryKey) {
            $query = substr_replace($query, 'INSERT IGNORE', 0, 6);
            return;
        }

        $updates = !empty($conflictUpdates) ? $conflictUpdates : $values;

        if ($primaryKey) {
            $lastInsertId = Query::raw(sprintf('LAST_INSERT_ID(%s)', $this->escapeTableOrColumn($primaryKey)));

            $updates = is_null($conflictUpdates)
                ? [$primaryKey => $lastInsertId]
                : [...$updates, $primaryKey => $lastInsertId];
        }

        $query .= sprintf(
            ' ON DUPLICATE KEY UPDATE %s',
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

    public function addReturning(string &$query, ?array $columns): void
    {
        return;
    }

    public function stringifyColumnDefinition(Column $column): string
    {
        $stringifiedColumn = parent::stringifyColumnDefinition($column);

        if ($column->autoIncrement && str_contains(strtolower($column->type), 'int')) {
            $stringifiedColumn .= ' AUTO_INCREMENT';
        }

        return $stringifiedColumn;
    }

    public function stringifyAlterTableAddColumn(AddColumn $addColumn): string
    {
        $stringifiedAddColumn = parent::stringifyAlterTableAddColumn($addColumn);

        if ($addColumn->autoIncrement && str_contains(strtolower($addColumn->type), 'int')) {
            $stringifiedAddColumn .= ' AUTO_INCREMENT';
        }

        return $stringifiedAddColumn;
    }

    public function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string
    {
        $stringifiedAlterColumn = parent::stringifyAlterTableAlterColumn($alterColumn);

        return substr_replace($stringifiedAlterColumn, 'MODIFY', 0, 5);
    }

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        if ($isPrimaryKey && $type == 'string') {
            return 'VARCHAR(64)';
        }

        if ($inConstraint && $type == 'string') {
            return 'VARCHAR(255)';
        }

        return [
            'bool' => 'TINYINT',
            'int' => 'INT',
            'float' => 'FLOAT',
            'string' => 'LONGTEXT',
            'DateTime' => 'DATETIME',
        ][$type];
    }
}
