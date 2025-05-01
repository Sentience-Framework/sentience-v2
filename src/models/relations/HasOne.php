<?php

namespace src\models\relations;

use src\database\Database;
use src\models\Model;

class HasOne extends Relation implements RelationInterface
{
    protected string $mToRJoinRegex = '/(\w+)\-\>(\w+)/';

    public function retrieve(Database $database, Model $model, ?callable $modifyQuery = null): ?Model
    {
        preg_match($this->mToRJoinRegex, $this->mToRJoin, $matches);

        [$modelProperty, $relationProperty] = array_slice($matches, 1);

        $query = $database->select()
            ->table($this->relationModel::getTable())
            ->whereEquals(
                $this->relationModel::getColumnByProperty($relationProperty),
                $model->{$modelProperty}
            )
            ->limit(1);

        $query = $this->modifyQuery($query, $modifyQuery);

        $results = $query->execute();

        $record = $results->fetch();

        if (!$record) {
            return null;
        }

        return $this->initModel($this->relationModel, $database, $record);
    }
}
