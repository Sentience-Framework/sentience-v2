<?php

namespace src\database\queries\traits;

use DateTime;
use src\database\queries\enums\Chain;
use src\database\queries\enums\WhereType;
use src\database\queries\objects\Condition;
use src\database\queries\objects\ConditionGroup;
use src\database\queries\Query;
use src\exceptions\QueryException;
use src\utils\Reflector;

trait Where
{
    protected array $where = [];

    public function whereEquals(string|array $column, mixed $value): static
    {
        return $this->equals($column, $value, Chain::AND);
    }

    public function whereNotEquals(string|array $column, mixed $value): static
    {
        return $this->notEquals($column, $value, Chain::AND);
    }

    public function whereLike(string|array $column, string $value): static
    {
        return $this->like($column, $value, Chain::AND);
    }

    public function whereNotLike(string|array $column, string $value): static
    {
        return $this->notLike($column, $value, Chain::AND);
    }

    public function whereStartsWith(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->startsWith($column, $value, $escapeBackslash, Chain::AND);
    }

    public function whereEndsWith(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->endsWith($column, $value, $escapeBackslash, Chain::AND);
    }

    public function whereContains(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->contains($column, $value, $escapeBackslash, Chain::AND);
    }

    public function whereNotContains(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->notContains($column, $value, $escapeBackslash, Chain::AND);
    }

    public function whereIn(string|array $column, array $values): static
    {
        return $this->in($column, $values, Chain::AND);
    }

    public function whereNotIn(string|array $column, array $values): static
    {
        return $this->notIn($column, $values, Chain::AND);
    }

    public function whereLessThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThan($column, $value, Chain::AND);
    }

    public function whereLessThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThanOrEquals($column, $value, Chain::AND);
    }

    public function whereGreaterThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThan($column, $value, Chain::AND);
    }

    public function whereGreaterThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThanOrEquals($column, $value, Chain::AND);
    }

    public function whereBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->between($column, $min, $max, Chain::AND);
    }

    public function whereNotBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->notBetween($column, $min, $max, Chain::AND);
    }

    public function whereIsNull(string|array $column): static
    {
        return $this->isNull($column, Chain::AND);
    }

    public function whereIsNotNull(string|array $column): static
    {
        return $this->isNotNull($column, Chain::AND);
    }

    public function whereEmpty(string|array $column): static
    {
        return $this->empty($column, Chain::AND);
    }

    public function whereNotEmpty(string|array $column): static
    {
        return $this->notEmpty($column, Chain::AND);
    }

    public function whereRegex(string|array $column, string $pattern): static
    {
        return $this->regex($column, $pattern, Chain::AND);
    }

    public function whereNotRegex(string|array $column, string $pattern): static
    {
        return $this->notRegex($column, $pattern, Chain::AND);
    }

    public function whereGroup(callable $callback): static
    {
        return $this->group($callback, Chain::AND);
    }

    public function where(string $expression, null|bool|int|float|string|DateTime ...$values): static
    {
        return $this->rawExpression($expression, $values, Chain::AND);
    }

    public function orWhereEquals(string|array $column, mixed $value): static
    {
        return $this->equals($column, $value, Chain::OR);
    }

    public function orWhereNotEquals(string|array $column, mixed $value): static
    {
        return $this->notEquals($column, $value, Chain::OR);
    }

    public function orWhereLike(string|array $column, string $value): static
    {
        return $this->like($column, $value, Chain::OR);
    }

    public function orWhereNotLike(string|array $column, string $value): static
    {
        return $this->notLike($column, $value, Chain::OR);
    }

    public function orWhereStartsWith(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->startsWith($column, $value, $escapeBackslash, Chain::OR);
    }

    public function orWhereEndsWith(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->endsWith($column, $value, $escapeBackslash, Chain::OR);
    }

    public function orWhereContains(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->contains($column, $value, $escapeBackslash, Chain::OR);
    }

    public function orWhereNotContains(string|array $column, string $value, bool $escapeBackslash = false): static
    {
        return $this->notContains($column, $value, $escapeBackslash, Chain::OR);
    }

    public function orWhereIn(string|array $column, array $values): static
    {
        return $this->in($column, $values, Chain::OR);
    }

    public function orWhereNotIn(string|array $column, array $values): static
    {
        return $this->notIn($column, $values, Chain::OR);
    }

    public function orWhereLessThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThan($column, $value, Chain::OR);
    }

    public function orWhereLessThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->lessThanOrEquals($column, $value, Chain::OR);
    }

    public function orWhereGreaterThan(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThan($column, $value, Chain::OR);
    }

    public function orWhereGreaterThanOrEquals(string|array $column, int|float|string|DateTime $value): static
    {
        return $this->greaterThanOrEquals($column, $value, Chain::OR);
    }

    public function orWhereBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->between($column, $min, $max, Chain::OR);
    }

    public function orWhereNotBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max): static
    {
        return $this->notBetween($column, $min, $max, Chain::OR);
    }

    public function orWhereIsNull(string|array $column): static
    {
        return $this->isNull($column, Chain::OR);
    }

    public function orWhereIsNotNull(string|array $column): static
    {
        return $this->isNotNull($column, Chain::OR);
    }

    public function orWhereEmpty(string|array $column): static
    {
        return $this->empty($column, Chain::AND);
    }

    public function orWhereNotEmpty(string|array $column): static
    {
        return $this->notEmpty($column, Chain::AND);
    }

    public function orWhereRegex(string|array $column, string $pattern): static
    {
        return $this->regex($column, $pattern, Chain::OR);
    }

    public function orWhereNotRegex(string|array $column, string $pattern): static
    {
        return $this->notRegex($column, $pattern, Chain::OR);
    }

    public function orWhereGroup(callable $callback): static
    {
        return $this->group($callback, Chain::OR);
    }

    public function orWhere(string $expression, null|bool|int|float|string|DateTime ...$values): static
    {
        return $this->rawExpression($expression, $values, Chain::OR);
    }

    protected function equals(string|array $column, mixed $value, Chain $chain): static
    {
        $this->addCondition(WhereType::EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function notEquals(string|array $column, mixed $value, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function like(string|array $column, string $value, Chain $chain): static
    {
        $this->addCondition(WhereType::LIKE, $column, $value, $chain);

        return $this;
    }

    protected function notLike(string|array $column, string $value, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_LIKE, $column, $value, $chain);

        return $this;
    }

    protected function startsWith(string|array $column, string $value, bool $escapeBackslash, Chain $chain): static
    {
        $this->like($column, Query::escapeLikeChars($value, $escapeBackslash) . '%', $chain);

        return $this;
    }

    protected function endsWith(string|array $column, string $value, bool $escapeBackslash, Chain $chain): static
    {
        $this->like($column, '%' . Query::escapeLikeChars($value, $escapeBackslash), $chain);

        return $this;
    }

    protected function contains(string|array $column, string $value, bool $escapeBackslash, Chain $chain): static
    {
        $this->like($column, '%' . Query::escapeLikeChars($value, $escapeBackslash) . '%', $chain);

        return $this;
    }

    protected function notContains(string|array $column, string $value, bool $escapeBackslash, Chain $chain): static
    {
        $this->notLike($column, '%' . Query::escapeLikeChars($value, $escapeBackslash) . '%', $chain);

        return $this;
    }

    protected function in(string|array $column, array $values, Chain $chain): static
    {
        $this->addCondition(WhereType::IN, $column, $values, $chain);

        return $this;
    }

    protected function notIn(string|array $column, array $values, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_IN, $column, $values, $chain);

        return $this;
    }

    protected function lessThan(string|array $column, int|float|string|DateTime $value, Chain $chain): static
    {
        $this->addCondition(WhereType::LESS_THAN, $column, $value, $chain);

        return $this;
    }

    protected function lessThanOrEquals(string|array $column, int|float|string|DateTime $value, Chain $chain): static
    {
        $this->addCondition(WhereType::LESS_THAN_OR_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function greaterThan(string|array $column, int|float|string|DateTime $value, Chain $chain): static
    {
        $this->addCondition(WhereType::GREATER_THAN, $column, $value, $chain);

        return $this;
    }

    protected function greaterThanOrEquals(string|array $column, int|float|string|DateTime $value, Chain $chain): static
    {
        $this->addCondition(WhereType::GREATER_THAN_OR_EQUALS, $column, $value, $chain);

        return $this;
    }

    protected function between(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max, Chain $chain): static
    {
        $this->addCondition(WhereType::BETWEEN, $column, [$min, $max], $chain);

        return $this;
    }

    protected function notBetween(string|array $column, int|float|string|DateTime $min, int|float|string|DateTime $max, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_BETWEEN, $column, [$min, $max], $chain);

        return $this;
    }

    protected function isNull(string|array $column, Chain $chain): static
    {
        $this->addCondition(WhereType::EQUALS, $column, null, $chain);

        return $this;
    }

    protected function isNotNull(string|array $column, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_EQUALS, $column, null, $chain);

        return $this;
    }

    protected function empty(string|array $column, Chain $chain): static
    {
        return $this->group(
            function (ConditionGroup $conditionGroup) use ($column): ConditionGroup {
                return $conditionGroup
                    ->orWhereIsNull($column)
                    ->orWhereEquals($column, 0)
                    ->orWhereEquals($column, '');
            },
            $chain
        );
    }

    protected function notEmpty(string|array $column, Chain $chain): static
    {
        return $this->group(
            function (ConditionGroup $conditionGroup) use ($column): ConditionGroup {
                return $conditionGroup
                    ->whereIsNotNull($column)
                    ->whereNotEquals($column, 0)
                    ->whereNotEquals($column, '');
            },
            $chain
        );
    }

    protected function regex(string|array $column, string $pattern, Chain $chain): static
    {
        $this->addCondition(WhereType::REGEX, $column, $pattern, $chain);

        return $this;
    }

    protected function notRegex(string|array $column, string $pattern, Chain $chain): static
    {
        $this->addCondition(WhereType::NOT_REGEX, $column, $pattern, $chain);

        return $this;
    }

    protected function group(callable $callback, Chain $chain): static
    {
        $conditionGroup = new ConditionGroup($chain);

        $conditionGroup = $callback($conditionGroup);

        if (!($conditionGroup instanceof ConditionGroup)) {
            throw new QueryException('callback must return %s', Reflector::getShortName(ConditionGroup::class));
        }

        if (count($conditionGroup->getConditions()) == 0) {
            return $this;
        }

        $this->where[] = $conditionGroup;

        return $this;
    }

    protected function rawExpression(string $expression, array $values, Chain $chain): static
    {
        $this->addCondition(WhereType::RAW, $expression, $values, $chain);

        return $this;
    }

    protected function addCondition(WhereType $type, string|array $query, mixed $value, Chain $chain): void
    {
        $this->where[] = new Condition($type, $query, $value, $chain);
    }
}
