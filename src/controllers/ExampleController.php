<?php

namespace src\controllers;

use src\database\Database;
use src\database\queries\Query;
use src\sentience\Request;
use src\sentience\Response;
use src\sentience\Stdio;
use src\utils\Json;

class ExampleController extends Controller
{
    protected ?Request $request;

    public function __construct(?Request $request)
    {
        $this->request = $request;
    }

    public function cliExample(array $words, array $flags): void
    {
        Stdio::printLn(Json::encode([
            'words' => $words,
            'flags' => $flags
        ]));
    }

    public function jsonResponse(): void
    {
        Response::ok(['key' => 'value'], 'json');
    }

    public function xmlResponse(): void
    {
        Response::ok(['key' => 'value'], 'xml');
    }

    public function urlResponse(): void
    {
        Response::ok(['key' => 'value'], 'url');
    }

    public function getUser(): void
    {
        Response::ok($this->request);
    }

    public function getContact(): void
    {
        Response::ok($this->request);
    }

    public function getContacts(): void
    {
        Response::ok($this->request);
    }

    public function createContact(): void
    {
        Response::ok($this->request);
    }

    public function updateContact(): void
    {
        Response::ok($this->request);
    }

    public function query(Database $database): void
    {
        $startTime = microtime(true);

        $queries = [];

        // $queries[] = $database->select()
        //     ->table(Query::alias(['public', 'table_1'], 'table1'))
        //     ->distinct()
        //     ->columns([
        //         'column1',
        //         Query::raw('CONCAT(column1, column2)'),
        //         Query::alias(
        //             Query::raw('column2'),
        //             'col2'
        //         )
        //     ])
        //     ->leftJoin(
        //         Query::alias('table2', 'jt'),
        //         'joinColumn',
        //         ['public', 't1'],
        //         't1Column'
        //     )
        //     ->rightJoin(
        //         Query::raw('table2'),
        //         'joinColumn',
        //         Query::raw('t1'),
        //         't1Column'
        //     )->innerJoin(
        //         ['public', 'table3'],
        //         'joinColumn',
        //         ['public', 'table1'],
        //         't1Column'
        //     )
        //     ->innerJoin(
        //         'table4',
        //         'joinColumn',
        //         'table1',
        //         't1Column'
        //     )
        //     ->join('RIGHT JOIN table2 jt ON jt.column1 = table1.column1 AND jt.column2 = table2.column2')
        //     ->whereEquals('column1', 10)
        //     ->whereGroup(function ($group) {
        //         return $group->whereGreaterThanOrEquals('column2', 20)
        //             ->orwhereIsNull('column3');
        //     })
        //     ->where('DATE(`created_at`) > now()')
        //     ->whereGroup(function ($group) {
        //         return $group->whereIn('column4', [1, 2, 3, 4])
        //             ->whereNotEquals('column5', 'test string');
        //     })
        //     ->whereGroup(function ($group) {
        //         return $group;
        //     })
        //     ->whereIn('column2', [])
        //     ->whereStartsWith('column2', 'a')
        //     ->whereEndsWith('column2', 'z')
        //     ->whereEmpty('empty_column')
        //     ->whereNotEmpty('not_empty_column')
        //     ->whereRegex('column6', 'file|read|write|open')
        //     ->whereNotRegex('column6', 'error')
        //     ->groupBy([
        //         ['table', 'column'],
        //         'column2',
        //         Query::raw('rawColumn')
        //     ])
        //     ->having('COUNT(*) > ?', 10)
        //     ->orderByAsc('column4')
        //     ->orderByDesc('column5')
        //     ->orderByAsc(Query::raw('column6'))
        //     ->orderByDesc(Query::raw('column7'))
        //     ->limit(1)
        //     ->offset(10)
        //     ->rawQuery();

        // $queries[] = $database->insert()
        //     ->table(Query::alias('table_1', 'table1'))
        //     ->values([
        //         'column1' => Query::now(),
        //         'column2' => true,
        //         'column3' => false,
        //         'column4' => Query::raw('column1 + 1')
        //     ])
        //     // ->onConflictUpdate(['id'], [], 'id')
        //     ->onConflictIgnore(['id'], 'id')
        //     ->returning(['id'])
        //     ->rawQuery();

        $queries[] = $database->update()
            ->table('table_1')
            ->values([
                'column1' => Query::now(),
                'column2' => true,
                'column3' => false,
                'column4' => Query::raw('column1 + 1')
            ])
            ->whereStartsWith('column2', '\\a\%')
            ->whereRegex('column6', 'file|read|write|open')
            ->returning(['id'])
            ->rawQuery();

        // $queries[] = $database->delete()
        //     ->table('table_1')
        //     ->whereBetween('column2', 10, 20)
        //     ->orWhereNotBetween('column2', 70, 80)
        //     ->returning(['id'])
        //     ->rawQuery();

        // $queries[] = $database->createTable()
        //     ->ifNotExists()
        //     ->table('table_1')
        //     ->column('primary_key', 'int', true, null, true)
        //     ->column('column1', 'bigint', true)
        //     ->column('column2', 'varchar(255)')
        //     ->primaryKeys(['primary_key'])
        //     ->uniqueConstraint(['column1', 'column2'])
        //     ->foreignKeyConstraint('column1', 'table_2', 'reference_column', 'fk_table_1')
        //     ->rawQuery();

        // $queries[] = $database->alterTable()
        //     ->table('table_1')
        //     ->addColumn('column3', 'INT')
        //     // ->alterColumn('column3', 'TEXT')
        //     ->renameColumn('column3', 'column4')
        //     ->dropColumn('column4')
        //     // ->addUniqueConstraint(['column1', 'column2'], 'unique_constraint')
        //     // ->addForeignKeyConstraint('column4', 'reference_table', 'reference_column')
        //     // ->dropConstraint('unique_constraint')
        //     ->rawQuery();

        // $queries[] = $database->dropTable()
        //     ->table('table_1')
        //     ->ifExists()
        //     ->rawQuery();

        foreach ($queries as $query) {
            Stdio::printLn($query);
            Stdio::printLn('');
        }

        $endTime = microtime(true);

        Stdio::printFLn('Time: %f', $endTime - $startTime);
    }
}
