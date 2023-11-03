<?php

namespace LiamH\ValueObjectCompiler\Factory;

use LiamH\ValueObjectCompiler\Generator\FileGenerator;
use LiamH\ValueObjectCompiler\Generator\SourceGenerator;
use LiamH\ValueObjectCompiler\Service\DecodedObjectService;

interface GeneratorFactory
{
    public function createSourceGenerator(): SourceGenerator;
    public function createFileGenerator(string $outputDirectory): FileGenerator;
    public function createDecodedObjectService(): DecodedObjectService;
}
