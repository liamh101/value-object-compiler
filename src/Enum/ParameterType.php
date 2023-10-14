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

    public function getDefinitionName(): string
    {
        return match ($this) {
            self::STRING => 'string',
            self::INTEGER => 'int',
            self::FLOAT => 'float',
            self::ARRAY => 'array',
            self::OBJECT => '',
            self::NULL => '?',
            self::BOOLEAN => 'bool',
        };
    }
}
