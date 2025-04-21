<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\containers\Raw;

class Pgsql extends Sql implements DialectInterface
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public function addConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, array $values, ?string $primaryKey): void
    {
        if (is_null($conflict)) {
            return;
        }

        $expression = is_string($conflict)
            ? sprintf('ON CONSTRAINT %s', $this->escapeTableOrColumn($conflict))
            : sprintf(
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

        $updates = !empty($conflictUpdates) ? $conflictUpdates : $values;

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

    public function castBool(bool $bool): mixed
    {
        return $bool ? 't' : 'f';
    }

    public function parseDateTime(?string $dateTimeString): ?DateTime
    {
        if (in_array(substr($dateTimeString, -3, 1), ['-', '+'])) {
            return parent::parseDateTime(sprintf('%s:00', $dateTimeString));
        }

        return parent::parseDateTime($dateTimeString);
    }

    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string
    {
        if ($isAutoIncrement && $type == 'int') {
            return 'SERIAL';
        }

        return [
            'bool' => 'BOOL',
            'int' => 'INT8',
            'float' => 'FLOAT8',
            'string' => 'TEXT',
            'DateTime' => 'TIMESTAMP',
        ][$type];
    }
}
