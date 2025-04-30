<?php

namespace src\database\queries;

use src\database\queries\containers\Raw;
use src\database\queries\traits\Limit;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;
use src\database\queries\traits\Where;

class Update extends Query implements QueryInterface
{
    use Limit;
    use Returning;
    use Table;
    use Values;
    use Where;

    public function build(): array
    {
        return $this->dialect->update([
            'table' => $this->table,
            'values' => $this->values,
            'where' => $this->where,
            'limit' => $this->limit,
            'returning' => $this->returning
        ]);
    }
}
