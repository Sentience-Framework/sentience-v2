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

    public function __construct(Database $database, PDOStatement $pdoStatement, string $query)
    {
        $this->database = $database;
        $this->pdoStatement = $pdoStatement;
        $this->query = $query;
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

        if (is_bool($object)) {
            return null;
        }

        return $object;
    }

    public function fetchAll(string $class = 'stdClass'): array
    {
        return $this->pdoStatement->fetchAll(PDO::FETCH_CLASS, $class);
    }

    public function fetchAssociative(): ?object
    {
        $associative = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

        if (is_bool($associative)) {
            return null;
        }

        return $associative;
    }

    public function fetchAllAssociative(): array
    {
        return $this->pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lastInsertId(?string $sequence = null): ?string
    {
        return $this->database->lastInsertId($sequence);
    }

    public function getPDOStatementAttribute(int $attribute): mixed
    {
        return $this->pdoStatement->getAttribute($attribute);
    }

    public function setPDOStatementAttribute(int $attribute, mixed $value): mixed
    {
        return $this->pdoStatement->setAttribute($attribute, $value);
    }
}
