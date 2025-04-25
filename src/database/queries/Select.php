<?php

namespace src\database\queries;

use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;
use src\database\queries\traits\Columns;
use src\database\queries\traits\Distinct;
use src\database\queries\traits\GroupBy;
use src\database\queries\traits\Having;
use src\database\queries\traits\Joins;
use src\database\queries\traits\Limit;
use src\database\queries\traits\Offset;
use src\database\queries\traits\OrderBy;
use src\database\queries\traits\Table;
use src\database\queries\traits\Where;

class Select extends Query implements QueryInterface
{
    use Columns;
    use Distinct;
    use GroupBy;
    use Having;
    use Joins;
    use Limit;
    use Offset;
    use OrderBy;
    use Table;
    use Where;

    public function build(): array
    {
        $query = '';
        $params = [];

        $query .= 'SELECT';

        if ($this->distinct) {
            $query .= ' DISTINCT';
        }

        $query .= ' ';
        $query .= count($this->columns) > 0
            ? implode(
                ', ',
                array_map(
                    function (string|array|Alias|Raw $column): string {
                        if (is_array($column)) {
                            return $this->dialect->escapeTableOrColumn($column);
                        }

                        if ($column instanceof Alias) {
                            return $this->dialect->escapeTableOrColumn($column->name, $column->alias);
                        }

                        if ($column instanceof Raw) {
                            return $column->expression;
                        }

                        return $this->dialect->escapeTableOrColumn($column);
                    },
                    $this->columns
                )
            )
            : '*';

        $query .= ' FROM';

        $this->dialect->addTable($query, $this->table, true);
        $this->dialect->addJoins($query, $this->joins);
        $this->dialect->addWhere($query, $params, $this->where);
        $this->dialect->addGroupBy($query, $this->groupBy);
        $this->dialect->addHaving($query, $params, $this->having, $this->havingValues);
        $this->dialect->addOrderBy($query, $this->orderBy);
        $this->dialect->addLimit($query, $this->limit);
        $this->dialect->addOffset($query, $this->limit, $this->offset);

        $query .= ';';

        return [$query, $params];
    }

    public function count(null|string|array|Raw $column = null): int
    {
        $previousColumns = $this->columns;
        $previousDistinct = $this->distinct;

        $this->distinct = false;

        $countExpression = !is_null($column)
            ? $this->dialect->escapeTableOrColumn($column)
            : '*';

        $this->columns([
            Query::alias(
                Query::raw(
                    sprintf(
                        'COUNT(%s)',
                        ($previousDistinct ? 'DISTINCT ' : '') . $countExpression
                    )
                ),
                'count'
            )
        ]);

        $count = (int) $this->execute()->fetch()->count;

        $this->columns = $previousColumns;
        $this->distinct = $previousDistinct;

        return $count;
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }
}
