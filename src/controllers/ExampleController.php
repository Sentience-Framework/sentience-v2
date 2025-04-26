<?php

namespace src\controllers;

use src\database\queries\Query;
use src\database\Database;
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

        $queries[] = $database->select()
            ->table(Query::alias(['public', 'table_1'], 'table1'))
            ->distinct()
            ->columns([
                'column1',
                Query::raw('CONCAT(column1, column2)'),
                Query::alias(
                    Query::raw('column2'),
                    'col2'
                ),
            ])
            ->leftJoin(
                Query::alias('table2', 'jt'),
                'joinColumn',
                ['public', 't1'],
                't1Column'
            )
            ->rightJoin(
                Query::raw('table2'),
                'joinColumn',
                Query::raw('t1'),
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
            ->join('RIGHT JOIN table2 jt ON jt.column1 = table1.column1 AND jt.column2 = table2.column2')
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
            ->whereGroup(function ($group) {
                return $group;
            })
            ->whereIn('column2', [])
            ->groupBy([
                ['table', 'column'],
                'column2',
                Query::raw('rawColumn')
            ])
            ->having('COUNT(*) > ?', 10)
            ->orderByAsc('column4')
            ->orderByDesc('column5')
            ->orderByAsc(Query::raw('column6'))
            ->orderByDesc(Query::raw('column7'))
            ->limit(1)
            ->offset(10)
            ->rawQuery();

        $queries[] = $database->insert()
            ->table(Query::alias('table_1', 'table1'))
            ->values([
                'column1' => Query::now(),
                'column2' => true,
                'column3' => false,
                'column4' => Query::raw('column1 + 1')
            ])
            // ->onConflictUpdate(['id'], [], 'id')
            ->onConflictIgnore(['id'], 'id')
            ->returning(['id'])
            ->rawQuery();

        $queries[] = $database->update()
            ->table('table_1')
            ->values([
                'column1' => Query::now(),
                'column2' => true,
                'column3' => false,
                'column4' => Query::raw('column1 + 1'),
            ])
            ->returning(['id'])
            ->rawQuery();

        $queries[] = $database->delete()
            ->table('table_1')
            ->whereBetween('column2', 10, 20)
            ->orWhereNotBetween('column2', 70, 80)
            ->returning(['id'])
            ->rawQuery();

        foreach ($queries as $query) {
            Stdio::printLn($query);
            Stdio::printLn('');
        }

        $endTime = microtime(true);

        Stdio::printFLn('Time: %f', $endTime - $startTime);
    }
}
