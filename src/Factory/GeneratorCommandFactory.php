<?php

namespace LiamH\Valueobjectgenerator\Factory;

use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\Service\NameService;

readonly class GeneratorCommandFactory implements GeneratorFactory
{
    public function createNameService(): NameService
    {
        return new NameService();
    }

    public function createSourceGenerator(): JsonGenerator
    {
        return new JsonGenerator($this->createNameService());
    }

    public function createFileService(): FileService
    {
        return new FileService();
    }

    public function createDecodedObjectService(): DecodedObjectService
    {
        return new DecodedObjectService();
    }

    public function createFileGenerator(): ValueObjectGenerator
    {
        return new ValueObjectGenerator($this->createDecodedObjectService(), $this->createFileService());
    }
}
