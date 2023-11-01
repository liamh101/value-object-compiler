<?php

namespace LiamH\Valueobjectgenerator\Factory;

use LiamH\Valueobjectgenerator\Generator\FileGenerator;
use LiamH\Valueobjectgenerator\Generator\SourceGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;

interface GeneratorFactory
{
    public function createSourceGenerator(): SourceGenerator;
    public function createFileGenerator(): FileGenerator;
    public function createDecodedObjectService(): DecodedObjectService;
}
