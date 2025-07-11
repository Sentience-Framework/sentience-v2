<?php

namespace src\database\queries\objects;

class Column
{
    public string $name;
    public string $type;
    public bool $notNull;
    public mixed $defaultValue;
    public bool $autoIncrement;

    public function __construct(string $name, string $type, bool $notNull = false, mixed $defaultValue = null, bool $autoIncrement = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->notNull = $notNull;
        $this->defaultValue = $defaultValue;
        $this->autoIncrement = $autoIncrement;
    }
}
