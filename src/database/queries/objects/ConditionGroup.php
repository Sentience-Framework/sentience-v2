<?php

namespace src\database\queries\objects;

use src\database\queries\enums\Chain;
use src\database\queries\traits\Where;

class ConditionGroup
{
    use Where;

    public Chain $chain;

    public function __construct(Chain $chain)
    {
        $this->chain = $chain;
    }

    public function getConditions(): array
    {
        return $this->where;
    }
}
