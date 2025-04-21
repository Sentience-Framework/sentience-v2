<?php

namespace src\database\queries\definitions;

class DropColumn
{
    public string $column;

    public function __construct(string $column)
    {
        $this->column = $column;
    }
}
