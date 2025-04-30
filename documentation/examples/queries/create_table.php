<?php

use src\database\Database;

/**
 * @var Database $database;
 */

$database->createTable()
    ->ifNotExists()
    ->table('table_1')
    ->column('id', 'INT', true, null, true)
    ->column('column1', 'VARCHAR(255)', true, 'empty')
    ->column('column2', 'DATETIME', true, 'now()')
    ->primaryKeys(['id'])
    ->uniqueConstraint(['column1', 'column2'], 'UQ_table_1')
    ->foreignKeyConstraint('column1', 'table1', 'column1')
    ->execute();
