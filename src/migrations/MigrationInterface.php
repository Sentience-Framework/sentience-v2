<?php

namespace src\migrations;

use src\database\Database;

interface MigrationInterface
{
    public function apply(Database $database): void;
    public function rollback(Database $database): void;
}
