<?php

namespace src\database\queries;

use src\database\queries\objects\QueryWithParams;
use src\database\queries\objects\Raw;
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

class Select extends Query
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

    public function build(): QueryWithParams
    {
        return $this->dialect->select([
            'distinct' => $this->distinct,
            'table' => $this->table,
            'columns' => $this->columns,
            'joins' => $this->joins,
            'where' => $this->where,
            'groupBy' => $this->groupBy,
            'having' => $this->having,
            'orderBy' => $this->orderBy,
            'limit' => $this->limit,
            'offset' => $this->offset
        ]);
    }

    public function count(null|string|array|Raw $column = null): int
    {
        $previousColumns = $this->columns;
        $previousDistinct = $this->distinct;

        $this->distinct = false;

        $countExpression = !is_null($column)
            ? $this->dialect->escapeIdentifier($column)
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
