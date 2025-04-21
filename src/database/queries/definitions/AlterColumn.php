<?php

namespace src\database\queries\definitions;

class AlterColumn
{
    public string $column;
    public string $options;

    public function __construct(string $column, string $options)
    {
        $this->column = $column;
        $this->options = $options;
    }
}
