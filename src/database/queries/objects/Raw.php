<?php

namespace src\database\queries\objects;

class Raw
{
    public string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }
}
