<?php

namespace src\database\queries\containers;

class Alias
{
    public string|array|Raw $name;
    public string $alias;

    public function __construct(string|array|Raw $name, string $alias)
    {
        $this->name = $name;
        $this->alias = $alias;
    }
}
