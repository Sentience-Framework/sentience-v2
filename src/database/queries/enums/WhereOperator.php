<?php

namespace src\database\queries\enums;

enum WhereOperator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '<>';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUALS = '<=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUALS = '>=';
    case BETWEEN = 'BETWEEN';
    case NOT_BETWEEN = 'NOT BETWEEN';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case REGEX = 'REGEX';
    case NOT_REGEX = 'NOT REGEX';
    case RAW = '';
}
