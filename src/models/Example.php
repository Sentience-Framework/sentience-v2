<?php

namespace src\models;

use src\database\Database;

class Example extends Model
{
    protected string $table = 'examples';
    protected string $primaryKey = 'id';
    protected bool $primaryKeyAutoIncrement = true;
    protected array $columns = [
        'id' => 'id',
        'exampleId' => 'example_id'
    ];

    public int $id;
    public int $exampleId;
    public Example $example;
    public array $examples;

    public function __construct(Database $database, ?object $record = null)
    {
        parent::__construct($database, $record);

        $this->hasOne('example', Example::class, 'id->exampleId');
        $this->belongsTo('example', Example::class, 'id<-exampleId');
        $this->hasMany('examples', Example::class, 'id-<exampleId');
        $this->manyToMany('examples', Example::class, 'example_has_example', 'id-<jt_column1:jt_column2>-exampleId');
    }
}
