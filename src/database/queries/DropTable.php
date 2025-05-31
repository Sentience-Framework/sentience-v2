<?php

namespace src\database\queries;

use src\database\queries\objects\QueryWithParams;
use src\database\queries\traits\IfExists;
use src\database\queries\traits\Table;

class DropTable extends Query
{
    use IfExists;
    use Table;

    public function build(): QueryWithParams
    {
        return $this->dialect->dropTable([
            'table' => $this->table,
            'ifExists' => $this->ifExists
        ]);
    }
}
