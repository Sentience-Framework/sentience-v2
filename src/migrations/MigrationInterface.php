<?php

namespace src\migrations;

use src\database\Database;

interface MigrationInterface
{
    public function up(Database $database): void;
    public function down(Database $database): void;
}
