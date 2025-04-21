<?php

namespace src\database\queries\traits;

trait Conflict
{
    protected null|string|array $conflict = null;
    protected ?array $conflictUpdates = null;
    protected ?string $conflictPrimaryKey = null;

    public function onConflictIgnore(string|array $conflict, ?string $primaryKey = null): static
    {
        $this->conflict = $conflict;
        $this->conflictUpdates = null;
        $this->conflictPrimaryKey = $primaryKey;

        return $this;
    }

    public function onConflictUpdate(string|array $conflict, array $updates = [], ?string $primaryKey = null): static
    {
        $this->conflict = $conflict;
        $this->conflictUpdates = $updates;
        $this->conflictPrimaryKey = $primaryKey;

        return $this;
    }
}
