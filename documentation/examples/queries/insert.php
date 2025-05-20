<?php

use src\database\Database;
use src\database\queries\Query;

/**
 * @var Database $database;
 */

$database->insert()
    ->table(Query::alias('table_1', 'table1'))
    ->values([
        'column1' => Query::now(),
        'column2' => true,
        'column3' => false,
        'column4' => Query::raw('column1 + 1')
    ])
    ->onConflictIgnore(['id'], 'id')
    ->onConflictUpdate(['id'], ['column1' => Query::now()], 'id')
    ->returning(['id'])
    ->execute();
