<?php

namespace src\models\relations;

use Closure;
use src\database\Database;
use src\database\queries\Select;
use src\exceptions\RelationException;
use src\models\Model;

abstract class Relation implements RelationInterface
{
    protected string $relationModel;
    protected string $mToRJoin;
    protected string $mToRJoinRegex;
    protected ?Closure $modifyDefaultQuery;

    public function __construct(string $relationModel, string $mToRJoin, ?callable $modifyDefaultQuery = null)
    {
        $isMatch = preg_match($this->mToRJoinRegex, $mToRJoin, $matches);

        if (!$isMatch) {
            throw new RelationException('%s is not a valid model to relation join', $mToRJoin);
        }

        $this->relationModel = $relationModel;
        $this->mToRJoin = $mToRJoin;
        $this->modifyDefaultQuery = $modifyDefaultQuery;
    }

    protected function modifyQuery(Select $query, ?callable $modifyQuery): Select
    {
        if ($this->modifyDefaultQuery) {
            $modifyDefaultQuery = $this->modifyDefaultQuery;
            $query = $modifyDefaultQuery($query);
        }

        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }

        return $query;
    }

    protected function initModel(string $class, Database $database, ?object $record): Model
    {
        return new $class($database, $record);
    }
}
