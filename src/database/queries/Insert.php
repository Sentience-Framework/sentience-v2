<?php

namespace src\database\queries;

use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Conflict;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;

class Insert extends Query implements QueryInterface
{
    use Conflict;
    use Returning;
    use Table;
    use Values;

    public function build(): array
    {
        return $this->dialect->insert([
            'table' => $this->table,
            'values' => $this->values,
            'conflict' => [
                'conflict' => $this->conflict,
                'updates' => $this->conflictUpdates,
                'primaryKey' => $this->conflictPrimaryKey
            ],
            'returning' => $this->returning
        ]);
    }
}
