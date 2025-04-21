<?php

namespace src\models;

use DateTime;

class Migration extends Model
{
    protected string $table = 'migrations';
    protected string $primaryKey = 'id';
    protected bool $primaryKeyAutoIncrement = true;
    protected array $columns = [
        'id' => 'id',
        'batch' => 'batch',
        'filename' => 'filename',
        'appliedAt' => 'applied_at'
    ];
    protected array $unique = ['filename'];

    public int $id;
    public int $batch;
    public string $filename;
    public DateTime $appliedAt;
}
