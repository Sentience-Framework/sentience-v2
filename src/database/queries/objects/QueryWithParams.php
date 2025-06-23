<?php

namespace src\database\queries\objects;

use src\database\dialects\DialectInterface;
use src\exceptions\QueryException;

class QueryWithParams
{
    public string $expression;
    public array $params;

    public function __construct(string $expression, array $params = [])
    {
        $this->expression = $expression;
        $this->params = $params;
    }

    public function toRawQuery(DialectInterface $dialect): string
    {
        if (count($this->params) == 0) {
            return $this->expression;
        }

        $params = array_map(
            function (mixed $param) use ($dialect): mixed {
                return $dialect->castToQuery($param);
            },
            $this->params
        );

        $index = 0;

        return preg_replace_callback(
            '/\?(?=(?:[^\'\"\`\\\\]|\'(?:\\\\.|[^\\\\\'])*\'|\"(?:\\\\.|[^\\\\\"])*\"|\`(?:\\\\.|[^\\\\\`])*\`)*$)/',
            function () use ($params, &$index): mixed {
                if (!key_exists($index, $params)) {
                    throw new QueryException('placeholder and value count do not match');
                }

                $param = $params[$index];

                $index++;

                return $param;
            },
            $this->expression
        );
    }
}
