<?php

namespace src\database\queries\containers;

class Raw
{
    public string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }
}
