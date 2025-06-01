<?php

namespace src\database\queries;

use src\database\queries\objects\Column;
use src\database\queries\objects\QueryWithParams;
use src\database\queries\traits\Constraints;
use src\database\queries\traits\IfNotExists;
use src\database\queries\traits\Table;

class CreateTable extends Query
{
    use Constraints;
    use IfNotExists;
    use Table;

    protected array $columns = [];
    protected array $primaryKeys = [];

    public function build(): QueryWithParams
    {
        return $this->dialect->createTable([
            'ifNotExists' => $this->ifNotExists,
            'table' => $this->table,
            'columns' => $this->columns,
            'primaryKeys' => $this->primaryKeys,
            'constraints' => [
                'unique' => $this->uniqueConstraints,
                'foreignKey' => $this->foreignKeyConstraints
            ]
        ]);
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
