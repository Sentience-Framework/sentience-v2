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
        $query = '';
        $params = [];

        $query .= 'DELETE FROM';

        $this->dialect->addTable($query, $this->table);
        $this->dialect->addWhere($query, $params, $this->where);
        $this->dialect->addReturning($query, $this->returning);

        $query .= ';';

        return [$query, $params];
    }
}
