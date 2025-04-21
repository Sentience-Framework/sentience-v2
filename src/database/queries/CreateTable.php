<?php

namespace src\database\queries;

use src\database\queries\containers\Raw;
use src\database\queries\traits\Columns;
use src\database\queries\traits\Constraints;
use src\database\queries\traits\IfNotExists;
use src\database\queries\traits\Table;

class CreateTable extends Query implements QueryInterface
{
    use Columns;
    use Constraints;
    use IfNotExists;
    use Table;

    protected array $primaryKeys = [];

    public function build(): array
    {
        $query = '';
        $params = [];

        $query .= 'CREATE TABLE';

        if ($this->ifNotExists) {
            $query .= ' IF NOT EXISTS';
        }

        $this->dialect->addTable($query, $this->table);

        $columnsInConstraints = [];

        foreach ($this->uniqueConstraints as $uniqueConstraint) {
            array_push($columnsInConstraints, ...$uniqueConstraint->columns);
        }

        foreach ($this->foreignKeyConstraints as $foreignKeyConstraint) {
            $columnsInConstraints[] = $foreignKeyConstraint->column;
        }

        $definitions = [];

        foreach ($this->columns as $column) {
            if ($column instanceof Raw) {
                $definitions[] = $column->expression;
            }

            $definitions[] = $this->dialect->stringifyColumnDefinition($column);
        }

        $definitions[] = sprintf(
            'PRIMARY KEY (%s)',
            implode(
                ', ',
                array_map(
                    function (string|Raw $column): string {
                        return $this->dialect->escapeTableOrColumn($column);
                    },
                    $this->primaryKeys
                )
            )
        );

        foreach ($this->uniqueConstraints as $uniqueConstraint) {
            $definitions[] = $this->dialect->stringifyUniqueConstraintDefinition($uniqueConstraint);
        }

        foreach ($this->foreignKeyConstraints as $foreignKeyConstraint) {
            $definitions[] = $this->dialect->stringifyForeignKeyConstraintDefinition($foreignKeyConstraint);
        }

        foreach ($this->rawConstraints as $rawConstraint) {
            $definitions[] = $rawConstraint->expression;
        }

        $query .= sprintf('(%s)', implode(', ', $definitions));
        $query .= ';';

        return [$query, $params];
    }

    public function primaryKeys(string|array $keys): static
    {
        $this->primaryKeys = is_string($keys)
            ? [$keys]
            : $keys;

        return $this;
    }
}
