<?php

namespace src\models\relations;

use src\database\Database;
use src\models\Model;

class HasMany extends Relation implements RelationInterface
{
    protected string $mToRJoinRegex = '/(\w+)\-\<(\w+)/';

    public function retrieve(Database $database, Model $model, callable $modifyQuery = null): array
    {
        preg_match($this->mToRJoinRegex, $this->mToRJoin, $matches);

        [$modelProperty, $relationProperty] = array_slice($matches, 1);

        $query = $database->select()
            ->table($this->relationModel::getTable())
            ->whereEquals(
                $this->relationModel::getColumnByProperty($relationProperty),
                $model->{$modelProperty}
            );

        $query = $this->modifyQuery($query, $modifyQuery);

        $results = $query->execute();

        $records = $results->fetchAll();

        if (count($records)) {
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
