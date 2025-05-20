<?php

namespace src\database\queries\objects;

class UniqueConstraint
{
    public array $columns;
    public ?string $name;

    public function __construct(array $columns, ?string $name = null)
    {
        $this->columns = $columns;
        $this->name = $name;
    }
}
