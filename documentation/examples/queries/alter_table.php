<?php

use src\database\Database;

/**
 * @var Database $database;
 */

$database->alterTable()
    ->table('table_1')
    ->addColumn('column3', 'TINYINT')
    ->alterColumn('column3', 'BIGINT')
    ->renameColumn('column3', 'column4')
    ->dropColumn('column4')
    ->execute();
