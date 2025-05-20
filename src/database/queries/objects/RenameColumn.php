<?php

namespace src\database\queries\objects;

class RenameColumn
{
    public string $oldName;
    public string $newName;

    public function __construct(string $oldName, string $newName)
    {
        $this->oldName = $oldName;
        $this->newName = $newName;
    }
}
