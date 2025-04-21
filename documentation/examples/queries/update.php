<?php

$database->update()
    ->table('table_1')
    ->values([
        'column1' => Query::now(),
        'column2' => true,
        'column3' => false,
        'column4' => Query::raw('column1 + 1'),
    ])
    ->where('column2 = ?', [false])
    ->returning(['id'])
    ->execute();
