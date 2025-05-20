<?php

namespace src\database\queries;

use src\database\queries\traits\OnConflict;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;

class Insert extends Query implements QueryInterface
{
    use OnConflict;
    use Returning;
    use Table;
    use Values;

    public function build(): array
    {
        return $this->dialect->insert([
            'table' => $this->table,
            'values' => $this->values,
            'onConflict' => [
                'conflict' => $this->onConflict,
                'updates' => $this->onConflictUpdates,
                'primaryKey' => $this->onConflictPrimaryKey
            ],
            'returning' => $this->returning
        ]);
    }
}
