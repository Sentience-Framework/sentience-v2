<?php

namespace src\models\relations;

use src\database\Database;
use src\models\Model;

interface RelationInterface
{
    public function retrieve(Database $database, Model $model, ?callable $modifyQuery = null): null|array|Model;
}
