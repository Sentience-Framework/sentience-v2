<?php

namespace src\database\queries\objects;

class AddPrimaryKeys
{
    public array $columns;

    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }
}
