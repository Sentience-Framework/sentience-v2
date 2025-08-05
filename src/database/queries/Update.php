<?php

namespace src\database\queries;

use src\database\queries\objects\QueryWithParams;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;
use src\database\queries\traits\Where;

class Update extends Query
{
    use Returning;
    use Table;
    use Values;
    use Where;

    public function build(): QueryWithParams
    {
        return $this->dialect->update([
            'table' => $this->table,
            'values' => $this->values,
            'where' => $this->where,
            'returning' => $this->returning
        ]);
    }
}
