<?php

namespace src\database\queries;

use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Where;

class Delete extends Query implements QueryInterface
{
    use Returning;
    use Table;
    use Where;

    public function build(): array
    {
        return $this->dialect->delete([
            'table' => $this->table,
            'where' => $this->where,
            'returning' => $this->returning
        ]);
    }
}
