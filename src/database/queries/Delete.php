<?php

namespace src\database\queries;

use src\database\queries\objects\QueryWithParams;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Where;

class Delete extends Query
{
    use Returning;
    use Table;
    use Where;

    public function build(): QueryWithParams
    {
        return $this->dialect->delete([
            'table' => $this->table,
            'where' => $this->where,
            'returning' => $this->returning
        ]);
    }
}
