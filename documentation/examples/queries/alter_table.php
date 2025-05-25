<?php

$database->alterTable()
    ->table('table_1')
    ->addColumn('column3', 'TINYINT')
    ->alterColumn('column3', 'BIGINT')
    ->renameColumn('column3', 'column4')
    ->dropColumn('column4')
    ->addUniqueConstraint(['column1', 'column2'], 'UQ_table_1_column1_column2')
    ->addForeignKeyConstraint('column4', 'reference_table', 'reference_column')
    ->dropConstraint('UQ_table_1_column1_column2')
    ->execute();
