<?php

namespace src\database\queries\traits;

use src\database\queries\containers\Raw;
use src\database\queries\definitions\ForeignKeyConstraint;
use src\database\queries\definitions\UniqueConstraint;

trait Constraints
{
    protected array $uniqueConstraints = [];
    protected array $foreignKeyConstraints = [];
    protected array $rawConstraints = [];

    public function uniqueConstraint(array $columns, ?string $name = null): static
    {
        $this->uniqueConstraints[] = new UniqueConstraint($columns, $name);

        return $this;
    }

    public function foreignKeyConstraint(string $column, string $referenceTable, string $referenceColumn): static
    {
        $this->foreignKeyConstraints[] = new ForeignKeyConstraint($column, $referenceTable, $referenceColumn);

        return $this;
    }

    public function constraint(string $expression): static
    {
        $this->rawConstraints[] = new Raw($expression);

        return $this;
    }
}
