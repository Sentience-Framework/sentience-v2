<?php

namespace src\database\queries\containers;

use src\database\queries\enums\WhereOperator;

class Condition
{
    public WhereOperator $type;
    public string|array $expression;
    public mixed $value;
    public WhereOperator $chain;

    public function __construct(WhereOperator $type, string|array $expression, mixed $value, WhereOperator $chain)
    {
        $this->type = $type;
        $this->expression = $expression;
        $this->value = $value;
        $this->chain = $chain;
    }
}
