<?php

namespace src\database;

use Closure;
use PDO;
use Throwable;
use src\database\dialects\DialectFactory;
use src\database\dialects\DialectInterface;
use src\database\queries\AlterTable;
use src\database\queries\CreateTable;
use src\database\queries\DropTable;
use src\database\queries\Delete;
use src\database\queries\Insert;
use src\database\queries\Select;
use src\database\queries\Update;
use src\exceptions\SqlException;

class Database
{
    protected string $dsn;
    protected ?Closure $debug;
    protected PDO $pdo;
    protected DialectInterface $dialect;

    public function __construct(string $dsn, ?callable $debug = null)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        $this->debug = $debug;

        $this->pdo = new PDO(
            $dsn,
            null,
            null,
            $options
        );

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $this->dialect = DialectFactory::fromDriver($driver);
    }

    public function unsafe(string $query): int
    {
        $startTime = microtime(true);

        $affected = $this->pdo->exec($query);

        $endTime = microtime(true);

        if (is_bool($affected)) {
            $error = implode(' ', $this->pdo->errorInfo());

            if ($this->debug) {
                ($this->debug)($query, null, null, $error);
            }

            throw new SqlException($error);
        }

        if ($this->debug) {
            ($this->debug)($query, $startTime, $endTime, null);
        }

        return $affected;
    }

    public function safe(string $query, array $params = []): Results
    {
        $rawQuery = $this->dialect->toRawQuery($query, $params);

        $startTime = microtime(true);

        $pdoStatement = $this->pdo->prepare($query);

        if (is_bool($pdoStatement)) {
            $error = implode(' ', $this->pdo->errorInfo());

            if ($this->debug) {
                ($this->debug)($rawQuery, null, null, $error);
            }

            throw new SqlException($error);
        }

        $success = $pdoStatement->execute(
            array_map(
                function (mixed $value): mixed {
                    return $this->dialect->castToDriver($value);
                },
                $params
            )
        );

        if (!$success) {
            $error = implode(' ', $pdoStatement->errorInfo());

            if ($this->debug) {
                ($this->debug)($rawQuery, null, null, $error);
            }

            throw new SqlException($error);
        }

        $endTime = microtime(true);

        if ($this->debug) {
            ($this->debug)($rawQuery, $startTime, $endTime, null);
        }

        return new Results(
            $this,
            $this->dialect,
            $pdoStatement,
            $rawQuery,
        );
    }

    public function beginTransaction(): void
    {
        if (!$this->pdo->beginTransaction()) {
            throw new SqlException(implode(' ', $this->pdo->errorInfo()));
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function commitTransaction(): void
    {
        if (!$this->inTransaction()) {
            return;
        }

        if (!$this->pdo->commit()) {
            throw new SqlException(implode(' ', $this->pdo->errorInfo()));
        }
    }

    public function rollbackTransaction(): void
    {
        if (!$this->inTransaction()) {
            return;
        }

        if (!$this->pdo->rollBack()) {
            throw new SqlException(implode(' ', $this->pdo->errorInfo()));
        }
    }

    public function transactionInCallback(callable $callback): void
    {
        $this->beginTransaction();

        try {
            $callback($this);

            $this->commitTransaction();
        } catch (Throwable $exception) {
            $this->rollbackTransaction();

            throw $exception;
        }
    }

    public function lastInsertId(?string $sequence = null): ?string
    {
        $lastInsertId = $this->pdo->lastInsertId($sequence);

        if (!$lastInsertId) {
            return null;
        }

        return $lastInsertId;
    }

    public function getPDOAttribute(int $attribute): mixed
    {
        return $this->pdo->getAttribute($attribute);
    }

    public function setPDOAttribute(int $attribute, mixed $value): mixed
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

    public function select(): Select
    {
        return new Select($this, $this->dialect);
    }

    public function insert(): Insert
    {
        return new Insert($this, $this->dialect);
    }

    public function update(): Update
    {
        return new Update($this, $this->dialect);
    }

    public function delete(): Delete
    {
        return new Delete($this, $this->dialect);
    }

    public function createTable(): CreateTable
    {
        return new CreateTable($this, $this->dialect);
    }

    public function alterTable(): AlterTable
    {
        return new AlterTable($this, $this->dialect);
    }

    public function dropTable(): DropTable
    {
        return new DropTable($this, $this->dialect);
    }
}
