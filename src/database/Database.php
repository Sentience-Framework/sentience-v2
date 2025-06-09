<?php

namespace src\database;

use Closure;
use PDO;
use Throwable;
use src\database\dialects\DialectFactory;
use src\database\dialects\DialectInterface;
use src\database\queries\AlterTable;
use src\database\queries\CreateTable;
use src\database\queries\Delete;
use src\database\queries\DropTable;
use src\database\queries\Insert;
use src\database\queries\objects\Alias;
use src\database\queries\objects\QueryWithParams;
use src\database\queries\objects\Raw;
use src\database\queries\Select;
use src\database\queries\Update;
use src\exceptions\SqlException;

class Database
{
    protected string $dsn;
    protected ?Closure $debug;
    protected PDO $pdo;
    protected DialectInterface $dialect;

    public function __construct(string $dsn, ?callable $debug = null, ?string $username = null, ?string $password = null)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ];

        $this->debug = $debug;

        $this->pdo = new PDO(
            $dsn,
            $username,
            $password,
            $options
        );

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $this->dialect = DialectFactory::fromDriver($driver);
    }

    public function unsafe(string $query): int
    {
        $startTime = microtime(true);

        $affected = $this->pdo->exec($query);

        if (is_bool($affected)) {
            $error = implode(' ', $this->pdo->errorInfo());

            if ($this->debug) {
                ($this->debug)($query, $startTime, $error);
            }

            throw new SqlException($error);
        }

        if ($this->debug) {
            ($this->debug)($query, $startTime, null);
        }

        return $affected;
    }

    public function safe(string $query, array $params = []): Results
    {
        $queryWithParams = new QueryWithParams($query, $params);

        $rawQuery = $queryWithParams->toRawQuery($this->dialect);

        $startTime = microtime(true);

        $pdoStatement = $this->pdo->prepare($query);

        if (is_bool($pdoStatement)) {
            $error = implode(' ', $this->pdo->errorInfo());

            if ($this->debug) {
                ($this->debug)($rawQuery, $startTime, $error);
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
                ($this->debug)($rawQuery, $startTime, $error);
            }

            throw new SqlException($error);
        }

        if ($this->debug) {
            ($this->debug)($rawQuery, $startTime, null);
        }

        return new Results(
            $this,
            $pdoStatement,
            $rawQuery
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

    public function select(null|string|array|Alias|Raw $table = null): Select
    {
        $query = new Select($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function insert(null|string|array|Alias|Raw $table = null): Insert
    {
        $query = new Insert($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function update(null|string|array|Alias|Raw $table = null): Update
    {
        $query = new Update($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function delete(null|string|array|Alias|Raw $table = null): Delete
    {
        $query = new Delete($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function createTable(null|string|array|Alias|Raw $table = null): CreateTable
    {
        $query = new CreateTable($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function alterTable(null|string|array|Alias|Raw $table = null): AlterTable
    {
        $query = new AlterTable($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }

    public function dropTable(null|string|array|Alias|Raw $table = null): DropTable
    {
        $query = new DropTable($this, $this->dialect);

        if (!is_null($table)) {
            $query->table($table);
        }

        return $query;
    }
}
