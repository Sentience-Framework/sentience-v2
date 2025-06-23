<?php

namespace src\models\relations;

use src\database\Database;
use src\models\Model;

class BelongsTo extends Relation implements RelationInterface
{
    protected string $mToRJoinRegexPattern = '/(.+)\<\-(.+)/';

    public function retrieve(Database $database, Model $model, ?callable $modifyQuery = null): ?Model
    {
        preg_match($this->mToRJoinRegexPattern, $this->mToRJoin, $matches);

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
