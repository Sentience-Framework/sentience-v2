<?php

namespace src\database\queries\enums;

enum JoinType: string
{
    case LEFT_JOIN = 'LEFT JOIN';
    case RIGHT_JOIN = 'RIGHT JOIN';
    case INNER_JOIN = 'INNER JOIN';
}
