<?php

namespace src\database\queries\objects;

use src\database\queries\enums\WhereOperator;
use src\database\queries\traits\Where;

class ConditionGroup
{
    use Where;

    public WhereOperator $chain;

    public function __construct(WhereOperator $chain)
    {
        $this->chain = $chain;
    }

    public function getConditions(): array
    {
        return $this->where;
    }
}
