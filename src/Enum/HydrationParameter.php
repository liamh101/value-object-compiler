<?php

namespace LiamH\ValueObjectCompiler\Enum;

enum HydrationParameter: string
{
    case ARRAY = 'array';
    case XML = '\SimpleXMLElement';
}
