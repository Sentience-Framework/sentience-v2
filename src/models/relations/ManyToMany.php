<?php

namespace src\models\relations;

use src\database\Database;
use src\database\queries\containers\Alias;
use src\database\queries\Query;
use src\models\Model;

class ManyToMany extends Relation implements RelationInterface
{
    protected string $mToRJoinRegex = '/(\w+)\-\<(\w+)\:(\w+)\>\-(\w+)/';
    protected string $junctionTable;

    public function __construct(string $relationModel, string $junctionTable, string $mToRJoin, ?callable $modifyDefaultQuery = null)
    {
        parent::__construct($relationModel, $mToRJoin, $modifyDefaultQuery);

        $this->junctionTable = $junctionTable;
    }

    public function retrieve(Database $database, Model $model, ?callable $modifyQuery = null): array
    {
        preg_match($this->mToRJoinRegex, $this->mToRJoin, $matches);

        [$modelProperty, $junctionTableModelColumn, $junctionTableRelationColumn, $relationProperty] = array_slice($matches, 1);

        $relationTable = $this->relationModel::getTable();

        $columns = array_map(
            function (string $column) use ($relationTable): Alias {
                return Query::alias([$relationTable, $column], $column);
            },
            $this->relationModel::getColumns()
        );

        $query = $database->select()
            ->table($this->junctionTable)
            ->columns($columns)
            ->leftJoin(
                $relationTable,
                $this->relationModel::getColumnByProperty($relationProperty),
                $this->junctionTable,
                $junctionTableRelationColumn,
            )
            ->whereEquals(
                [$this->junctionTable, $junctionTableModelColumn],
                $model->{$modelProperty},
            );

        $query = $this->modifyQuery($query, $modifyQuery);

        $results = $query->execute();

        $records = $results->fetchAll();

        if (count($records) == 0) {
            return $records;
        }

        return array_map(
            function (object $record) use ($database): Model {
                return $this->initModel($this->relationModel, $database, $record);
            },
            $records
        );
    }
}
