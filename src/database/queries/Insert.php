<?php

namespace src\database\queries;

use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Conflict;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;

class Insert extends Query implements QueryInterface
{
    use Conflict;
    use Returning;
    use Table;
    use Values;

    public function build(): array
    {
        $query = '';
        $params = [];

        $query .= 'INSERT INTO';

        $this->dialect->addTable($query, $this->table);

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
                            return $this->dialect->escapeTableOrColumn($column->name);
                        }

                        return $this->dialect->escapeTableOrColumn($column);
                    },
                    array_keys($this->values)
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
                    $this->values
                )
            )
        );

        $this->dialect->addConflict($query, $params, $this->conflict, $this->conflictUpdates, $this->conflictPrimaryKey, $this->values);
        $this->dialect->addReturning($query, $this->returning);

        $query .= ';';

        return [$query, $params];
    }
}
