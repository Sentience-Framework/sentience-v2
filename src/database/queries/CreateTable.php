<?php

namespace src\database\queries;

use src\database\queries\containers\Raw;
use src\database\queries\definitions\Column;
use src\database\queries\traits\Constraints;
use src\database\queries\traits\IfNotExists;
use src\database\queries\traits\Table;

class CreateTable extends Query implements QueryInterface
{
    use Constraints;
    use IfNotExists;
    use Table;

    protected array $columns = [];
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

        $definitions = [];

        foreach ($this->columns as $column) {
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

        $query .= sprintf(' (%s)', implode(', ', $definitions));
        $query .= ';';

        return [$query, $params];
    }

    public function column(string $name, string $type, bool $notNull = false, mixed $defaultValue = null, bool $autoIncrement = false): static
    {
        $this->columns[] = new Column($name, $type, $notNull, $defaultValue, $autoIncrement);

        return $this;
    }

    public function primaryKeys(string|array $keys): static
    {
        $this->primaryKeys = is_string($keys)
            ? [$keys]
            : $keys;

        return $this;
    }
}
