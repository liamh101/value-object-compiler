<?php

namespace LiamH\ValueObjectCompiler\Factory;

use LiamH\ValueObjectCompiler\Generator\JsonGenerator;
use LiamH\ValueObjectCompiler\Generator\ValueObjectGenerator;
use LiamH\ValueObjectCompiler\Service\JsonDecodedObjectService;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\Service\NameService;

class JsonGeneratorCommandFactory implements GeneratorFactory
{
    public function createNameService(): NameService
    {
        return new NameService();
    }

    public function createSourceGenerator(): JsonGenerator
    {
        return new JsonGenerator($this->createNameService());
    }

    public function createFileService(string $outputDirectory): FileService
    {
        return new FileService($outputDirectory);
    }

    public function createDecodedObjectService(): JsonDecodedObjectService
    {
        return new JsonDecodedObjectService();
    }

    public function createFileGenerator(string $outputDirectory): ValueObjectGenerator
    {
        return new ValueObjectGenerator($this->createDecodedObjectService(), $this->createFileService($outputDirectory));
    }
}
