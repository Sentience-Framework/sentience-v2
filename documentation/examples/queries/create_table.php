<?php

$database->createTable()
    ->ifNotExists()
    ->table('table_1')
    ->columns([
        new Column('id', 'INT', true, null, true),
        new Column('column1', 'VARCHAR(255)', true, 'empty'),
        new Column('column2', 'DATETIME', true, 'now()'),
    ])
    ->uniqueConstraint(['column1', 'column2'], 'UQ_table_1')
    ->foreighKeyConstraint('column1', 'table1', 'column1')
    ->primaryKeys(['id'])
    ->execute();
