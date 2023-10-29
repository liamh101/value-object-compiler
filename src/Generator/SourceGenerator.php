<?php

namespace LiamH\Valueobjectgenerator\Generator;

use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;

interface SourceGenerator
{
    public function generateClassFromSource(string $parentName, string $source): DecodedObject;
}
