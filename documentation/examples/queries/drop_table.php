<?php

use src\database\Database;

/**
 * @var Database $database;
 */

$database->dropTable()
    ->ifExists()
    ->table('table_1')
    ->rawQuery();
