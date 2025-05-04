<?php

namespace src\database\queries\traits;

trait OnConflict
{
    protected null|string|array $onConflict = null;
    protected ?array $onConflictUpdates = null;
    protected ?string $onConflictPrimaryKey = null;

    public function onConflictIgnore(string|array $conflict, ?string $primaryKey = null): static
    {
        $this->onConflict = $conflict;
        $this->onConflictUpdates = null;
        $this->onConflictPrimaryKey = $primaryKey;

        return $this;
    }

    public function onConflictUpdate(string|array $conflict, array $updates = [], ?string $primaryKey = null): static
    {
        $this->onConflict = $conflict;
        $this->onConflictUpdates = $updates;
        $this->onConflictPrimaryKey = $primaryKey;

        return $this;
    }
}
