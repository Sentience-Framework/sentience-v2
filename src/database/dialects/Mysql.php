<?php

namespace src\database\dialects;

use src\database\queries\objects\AlterColumn;
use src\database\queries\objects\Column;
use src\database\queries\objects\DropConstraint;
use src\database\queries\objects\QueryWithParams;
use src\database\queries\objects\Raw;
use src\database\queries\Query;

class Mysql extends Sql implements DialectInterface
{
    public const TABLE_OR_COLUMN_ESCAPE = '`';
    public const STRING_ESCAPE = '"';

    public function createTable(array $config): QueryWithParams
    {
        $queryWithParams = parent::createTable($config);

        $queryWithParams->expression = substr_replace(
            $queryWithParams->expression,
            ' ENGINE=InnoDB;',
            -1
        );

        return $queryWithParams;
    }

    public function addOnConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, ?string $primaryKey, array $insertValues): void
    {
        if (is_null($conflict)) {
            return;
        }

        if (is_null($conflictUpdates) && !$primaryKey) {
            $query = substr_replace($query, 'INSERT IGNORE', 0, 6);
            return;
        }

        $updates = !empty($conflictUpdates) ? $conflictUpdates : $insertValues;

        if ($primaryKey) {
            $lastInsertId = Query::raw(sprintf('LAST_INSERT_ID(%s)', $this->escapeIdentifier($primaryKey)));

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

    public function addReturning(string &$query, ?array $returning): void
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

    public function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string
    {
        $stringifiedAlterColumn = parent::stringifyAlterTableAlterColumn($alterColumn);

        return substr_replace($stringifiedAlterColumn, 'MODIFY', 0, 5);
    }

    public function stringifyAlterTableDropConstraint(DropConstraint $dropConstraint): string
    {
        $stringifiedDropConstraint = parent::stringifyAlterTableDropConstraint($dropConstraint);

        return substr_replace($stringifiedDropConstraint, 'INDEX', 5, 10);
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
            'DateTime' => 'DATETIME(6)'
        ][$type];
    }
}
