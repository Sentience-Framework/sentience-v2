<?php

$database->select()
    ->table(Query::alias(['public', 'table_1'], 'table1'))
    ->distinct()
    ->columns([
        'column1',
        Query::raw('CONCAT(column1, column2)'),
        Query::alias(
            Query::raw('column2'),
            'col2'
        )
    ])
    ->leftJoin(
        Query::alias('(ALIAS)table2', 'jt'),
        'joinColumn',
        't1',
        't1Column'
    )
    ->rightJoin(
        Query::raw('table2'),
        'joinColumn',
        't1',
        't1Column'
    )->innerJoin(
        ['public', 'table3'],
        'joinColumn',
        ['public', 'table1'],
        't1Column'
    )
    ->innerJoin(
        'table4',
        'joinColumn',
        'table1',
        't1Column'
    )
    ->join('LEFT JOIN (RAW)table2 jt ON jt.column1 = table1 AND jt.column2 = table2')
    ->whereEquals('column1', 10)
    ->whereGroup(function ($group) {
        return $group->whereGreaterThanOrEquals('column2', 20)
            ->orwhereIsNull('column3');
    })
    ->where('DATE(`created_at`) > now()')
    ->whereGroup(function ($group) {
        return $group->whereIn('column4', [1, 2, 3, 4])
            ->whereNotEquals('column5', 'test string');
    })
    ->whereIn('column2', [])
    ->whereNotIn('column2', [])
    ->whereStartsWith('column2', 'a')
    ->whereEndsWith('column2', 'z')
    ->whereEmpty('empty_column')
    ->whereNotEmpty('not_empty_column')
    ->whereRegex('column6', 'file|read|write|open')
    ->whereNotRegex('column6', 'error')
    ->groupBy([
        ['table', 'column'],
        'column2'
    ])
    ->having('COUNT(*) > ?', 10)
    ->orderByAsc('column4')
    ->orderByDesc('column5')
    ->limit(1)
    ->offset(10)
    ->execute();
