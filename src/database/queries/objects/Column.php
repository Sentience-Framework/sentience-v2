<?php

namespace src\database\queries\objects;

class Column
{
    public string $name;
    public string $type;
    public bool $notNull = false;
    public ?string $defaultValue = null;
    public bool $autoIncrement = false;

    public function __construct(string $name, string $type, bool $notNull = false, mixed $defaultValue = null, bool $autoIncrement = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->notNull = $notNull;
        $this->defaultValue = $defaultValue;
        $this->autoIncrement = $autoIncrement;
    }
}
