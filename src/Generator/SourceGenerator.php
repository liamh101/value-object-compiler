<?php

namespace LiamH\ValueObjectCompiler\Generator;

use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;

interface SourceGenerator
{
    public function generateClassFromSource(string $parentName, string $source): DecodedObject;
}
