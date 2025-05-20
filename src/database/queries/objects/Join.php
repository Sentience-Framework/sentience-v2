<?php

namespace src\database\queries\objects;

use src\database\queries\enums\JoinType;

class Join
{
    public JoinType $type;
    public string|array|Raw $joinTable;
    public ?string $joinTableAlias = null;
    public string $joinTableColumn;
    public string|array|Raw $onTable;
    public string $onTableColumn;

    public function __construct(JoinType $type, string|array|Alias|Raw $joinTable, string $joinTableColumn, string|array|Raw $onTable, string $onTableColumn)
    {
        if ($joinTable instanceof Alias) {
            $this->joinTable = $joinTable->name;
            $this->joinTableAlias = $joinTable->alias;
        } else {
            $this->joinTable = $joinTable;
        }

        $this->type = $type;
        $this->joinTableColumn = $joinTableColumn;
        $this->onTable = $onTable;
        $this->onTableColumn = $onTableColumn;
    }
}
