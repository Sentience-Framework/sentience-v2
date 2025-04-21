<?php

namespace src\database\queries;

use src\database\queries\containers\Raw;
use src\database\queries\traits\Limit;
use src\database\queries\traits\Returning;
use src\database\queries\traits\Table;
use src\database\queries\traits\Values;
use src\database\queries\traits\Where;

class Update extends Query implements QueryInterface
{
    use Limit;
    use Returning;
    use Table;
    use Values;
    use Where;

    public function build(): array
    {
        $query = '';
        $params = [];

        $query .= 'UPDATE';

        $this->dialect->addTable($query, $this->table);

        $query .= ' SET ';

        $query .= implode(
            ', ',
            array_map(
                function (mixed $value, string $key) use (&$params): string {
                    if ($value instanceof Raw) {
                        return sprintf(
                            '%s = %s',
                            $this->dialect->escapeTableOrColumn($key),
                            $value->expression
                        );
                    }

                    $params[] = $value;

                    return sprintf('%s = ?', $this->dialect->escapeTableOrColumn($key));
                },
                $this->values,
                array_keys($this->values)
            )
        );

        $this->dialect->addWhere($query, $params, $this->where);
        $this->dialect->addLimit($query, $this->limit);
        $this->dialect->addReturning($query, $this->returning);

        $query .= ';';

        return [$query, $params];
    }
}
