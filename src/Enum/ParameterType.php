<?php

namespace LiamH\Valueobjectgenerator\Enum;

enum ParameterType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'double';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case NULL = 'NULL';
    case BOOLEAN = 'boolean';
}
