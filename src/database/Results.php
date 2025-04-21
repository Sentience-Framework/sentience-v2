<?php

namespace src\database;

use PDO;
use PDOStatement;
use src\database\dialects\DialectInterface;

class Results
{
    protected Database $database;
    protected DialectInterface $dialect;
    protected PDOStatement $pdoStatement;
    public string $query;
    public array $params;

    public function __construct(Database $database, DialectInterface $dialect, PDOStatement $pdoStatement, string $query, array $params)
    {
        $this->database = $database;
        $this->dialect = $dialect;
        $this->pdoStatement = $pdoStatement;
        $this->query = $query;
        $this->params = $params;
    }

    public function countRows(): int
    {
        return $this->pdoStatement->rowCount();
    }

    public function countColumns(): int
    {
        return $this->pdoStatement->columnCount();
    }

    public function fetch(string $class = 'stdClass'): ?object
    {
        $object = $this->pdoStatement->fetchObject($class);

        if (!$object) {
            return null;
        }

        return $object;
    }

    public function fetchAll(string $class = 'stdClass'): array
    {
        return $this->pdoStatement->fetchAll(PDO::FETCH_CLASS, $class);
    }

    public function lastInsertId(?string $sequence = null): ?string
    {
        return $this->database->lastInsertId($sequence);
    }

    public function getPDOStatementAttribute(int $attribute): mixed
    {
        return $this->pdoStatement->getAttribute($attribute);
    }
}
