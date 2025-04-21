<?php

namespace src\database\queries\traits;

trait IfExists
{
    protected bool $ifExists = false;

    public function ifExists(): static
    {
        $this->ifExists = true;

        return $this;
    }
}
