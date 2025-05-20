<?php

namespace src\database\queries\objects;

class ForeignKeyConstraint
{
    public string $column;
    public string $referenceTable;
    public string $referenceColumn;
    public ?string $name;

    public function __construct(string $column, string $referenceTable, string $referenceColumn, ?string $name = null)
    {
        $this->column = $column;
        $this->referenceTable = $referenceTable;
        $this->referenceColumn = $referenceColumn;
        $this->name = $name;
    }
}
