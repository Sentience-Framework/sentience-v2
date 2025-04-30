<?php

namespace src\database\queries\definitions;

class DropConstraint
{
    public string $constraint;

    public function __construct(string $constraint)
    {
        $this->constraint = $constraint;
    }
}
