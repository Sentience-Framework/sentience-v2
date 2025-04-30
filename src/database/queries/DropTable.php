<?php

namespace src\database\queries;

use src\database\queries\traits\IfExists;
use src\database\queries\traits\Table;

class DropTable extends Query implements QueryInterface
{
    use IfExists;
    use Table;

    public function build(): array
    {
        return $this->dialect->dropTable([
            'table' => $this->table,
            'ifExists' => $this->ifExists
        ]);
    }
}
