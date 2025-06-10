<?php

namespace src\database\queries\objects;

use src\database\queries\enums\Chain;
use src\database\queries\enums\WhereType;

class Condition
{
    public WhereType $type;
    public string|array $expression;
    public mixed $value;
    public Chain $chain;

    public function __construct(WhereType $type, string|array $expression, mixed $value, Chain $chain)
    {
        $this->type = $type;
        $this->expression = $expression;
        $this->value = $value;
        $this->chain = $chain;
    }
}
