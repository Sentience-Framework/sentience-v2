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
        $query = '';
        $params = [];

        $query .= 'DROP TABLE';

        if ($this->ifExists) {
            $query .= ' IF EXISTS';
        }

        $this->dialect->addTable($query, $this->table);

        $query .= ';';

        return [$query, $params];
    }
}
