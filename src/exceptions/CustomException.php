<?php

namespace src\exceptions;

use Exception;

class CustomException extends Exception
{
    public function __construct(string $format, mixed ...$values)
    {
        parent::__construct(sprintf($format, ...$values));
    }
}
