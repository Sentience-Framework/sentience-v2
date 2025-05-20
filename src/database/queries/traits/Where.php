<?php

namespace src\database\queries\traits;

use DateTime;
use src\database\queries\enums\WhereOperator;
use src\database\queries\objects\Condition;
use src\database\queries\objects\ConditionGroup;
use src\exceptions\QueryException;
use src\utils\Reflector;

trait Where
{
    /**
     * @var Condition|ConditionGroup;
     */
    protected array $where = [];

    public function whereEquals(string|array $column, mixed $value): static
    {
        return $this->equals($column, $value, WhereOperator::AND);
    }

    public function whereNotEquals(string|array $column, mixed $value): static
    {
        return $this->notEquals($column, $value, WhereOperator::AND);
    }

    public function whereLike(string|array $column, string $value): static
    {
        return $this->like($column, $value, WhereOperator::AND);
    }

    public function whereNotLike(string|array $column, string $value): static
    {
        return $this->notLike($column, $value, WhereOperator::AND);
    }

    public function whereIn(string|array $column, array $values, bool $preventSqlSyntaxError = true): static
    {
        return $this->in($column, $values, $preventSqlSyntaxError, WhereOperator::AND);
    }

    public function whereNotIn(string|array $column, array $values, bool $preventSqlSyntaxError = true): static
    {
        return $this->notIn($column, $values, $preventSqlSyntaxError, WhereOperator::AND);
    }

    public function whereLessThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThan($column, $value, WhereOperator::AND);
    }

    public function whereLessThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThanOrEquals($column, $value, WhereOperator::AND);
    }

    public function whereGreaterThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThan($column, $value, WhereOperator::AND);
    }

    public function whereGreaterThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThanOrEquals($column, $value, WhereOperator::AND);
    }

    public function whereBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->between($column, $min, $max, WhereOperator::AND);
    }

    public function whereNotBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->notBetween($column, $min, $max, WhereOperator::AND);
    }

    public function whereIsNull(string|array $column): static
    {
        return $this->isNull($column, WhereOperator::AND);
    }

    public function whereIsNotNull(string|array $column): static
    {
        return $this->isNotNull($column, WhereOperator::AND);
    }

    public function whereGroup(callable $callback, bool $preventSqlSyntaxError = true): static
    {
        return $this->group($callback, $preventSqlSyntaxError, WhereOperator::AND);
    }

    public function where(string $expression, bool|int|float|string|DateTime ...$values): static
    {
        return $this->rawExpression($expression, $values, WhereOperator::AND);
    }

    public function orWhereEquals(string|array $column, mixed $value): static
    {
        return $this->equals($column, $value, WhereOperator::OR);
    }

    public function orWhereNotEquals(string|array $column, mixed $value): static
    {
        return $this->notEquals($column, $value, WhereOperator::OR);
    }

    public function orWhereLike(string|array $column, string $value): static
    {
        return $this->like($column, $value, WhereOperator::OR);
    }

    public function orWhereNotLike(string|array $column, string $value): static
    {
        return $this->notLike($column, $value, WhereOperator::OR);
    }

    public function orWhereIn(string|array $column, array $values, bool $preventSqlSyntaxError = true): static
    {
        return $this->in($column, $values, $preventSqlSyntaxError, WhereOperator::OR);
    }

    public function orWhereNotIn(string|array $column, array $values, bool $preventSqlSyntaxError = true): static
    {
        return $this->notIn($column, $values, $preventSqlSyntaxError, WhereOperator::OR);
    }

    public function orWhereLessThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThan($column, $value, WhereOperator::OR);
    }

    public function orWhereLessThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThanOrEquals($column, $value, WhereOperator::OR);
    }

    public function orWhereGreaterThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThan($column, $value, WhereOperator::OR);
    }

    public function orWhereGreaterThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThanOrEquals($column, $value, WhereOperator::OR);
    }

    public function orWhereBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->between($column, $min, $max, WhereOperator::OR);
    }

    public function orWhereNotBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->notBetween($column, $min, $max, WhereOperator::OR);
    }

    public function orWhereIsNull(string|array $column): static
    {
        return $this->isNull($column, WhereOperator::OR);
    }

    public function orWhereIsNotNull(string|array $column): static
    {
        return $this->isNotNull($column, WhereOperator::OR);
    }

    public function orWhereGroup(callable $callback, bool $preventSqlSyntaxError = true): static
    {
        return $this->group($callback, $preventSqlSyntaxError, WhereOperator::OR);
    }

    public function orWhere(string $expression, bool|int|float|string|DateTime ...$values): static
    {
        return $this->rawExpression($expression, $values, WhereOperator::OR);
    }

    protected function equals(string|array $column, mixed $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function notEquals(string|array $column, mixed $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::NOT_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function like(string|array $column, string $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::LIKE, $column, $value, $chain);

        return $this;
    }

    protected function notLike(string|array $column, string $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::NOT_LIKE, $column, $value, $chain);

        return $this;
    }

    protected function in(string|array $column, array $values, bool $preventSqlSyntaxError, WhereOperator $chain): static
    {
        if ($preventSqlSyntaxError && count($values) == 0) {
            return $this;
        }

        $this->addCondition(WhereOperator::IN, $column, $values, $chain);

        return $this;
    }

    protected function notIn(string|array $column, array $values, bool $preventSqlSyntaxError, WhereOperator $chain): static
    {
        if ($preventSqlSyntaxError && count($values) == 0) {
            return $this;
        }

        $this->addCondition(WhereOperator::NOT_IN, $column, $values, $chain);

        return $this;
    }

    protected function lessThan(string|array $column, int|float|string|DateTime $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::LESS_THAN, $column, $value, $chain);

        return $this;
    }

    protected function lessThanOrEquals(string|array $column, int|float|string|DateTime $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::LESS_THAN_OR_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function greaterThan(string|array $column, int|float|string|DateTime $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::GREATER_THAN, $column, $value, $chain);

        return $this;
    }

    protected function greaterThanOrEquals(string|array $column, int|float|string|DateTime $value, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::GREATER_THAN_OR_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function between(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::BETWEEN, $column, [$min, $max], $chain);

        return $this;
    }

    protected function notBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::NOT_BETWEEN, $column, [$min, $max], $chain);

        return $this;
    }

    protected function isNull(string|array $column, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::EQUALS, $column, null, $chain);

        return $this;
    }

    protected function isNotNull(string|array $column, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::NOT_EQUALS, $column, null, $chain);

        return $this;
    }

    protected function group(callable $callback, bool $preventSqlSyntaxError, WhereOperator $chain): static
    {
        $conditionGroup = new ConditionGroup($chain);

        $conditionGroup = $callback($conditionGroup);

        if (!($conditionGroup instanceof ConditionGroup)) {
            throw new QueryException('callback must return %s', Reflector::getShortName(ConditionGroup::class));
        }

        if ($preventSqlSyntaxError && count($conditionGroup->getConditions()) == 0) {
            return $this;
        }

        $this->where[] = $conditionGroup;

        return $this;
    }

    protected function rawExpression(string $expression, array $values, WhereOperator $chain): static
    {
        $this->addCondition(WhereOperator::RAW, $expression, $values, $chain);

        return $this;
    }

    protected function addCondition(WhereOperator $operator, string|array $query, mixed $value, WhereOperator $chain): void
    {
        $this->where[] = new Condition($operator, $query, $value, $chain);
    }
}
