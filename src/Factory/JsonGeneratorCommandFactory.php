<?php

namespace LiamH\Valueobjectgenerator\Factory;

use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\JsonDecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\Service\NameService;

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
