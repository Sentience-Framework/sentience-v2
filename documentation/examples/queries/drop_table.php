<?php

$database->dropTable()
    ->ifExists()
    ->table('table_1')
    ->rawQuery();
