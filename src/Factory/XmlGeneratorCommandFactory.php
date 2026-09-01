<?php

namespace LiamH\ValueObjectCompiler\Factory;

use LiamH\ValueObjectCompiler\Generator\XmlGenerator;
use LiamH\ValueObjectCompiler\Generator\ValueObjectGenerator;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\Service\NameService;
use LiamH\ValueObjectCompiler\Service\XmlDecodedObjectService;

class XmlGeneratorCommandFactory implements GeneratorFactory
{
    public function createNameService(): NameService
    {
        return new NameService();
    }

    public function createSourceGenerator(): XmlGenerator
    {
        return new XmlGenerator($this->createNameService());
    }

    public function createFileService(string $outputDirectory): FileService
    {
        return new FileService($outputDirectory);
    }

    public function createDecodedObjectService(): XmlDecodedObjectService
    {
        return new XmlDecodedObjectService();
    }

    public function createFileGenerator(string $outputDirectory): ValueObjectGenerator
    {
        return new ValueObjectGenerator($this->createDecodedObjectService(), $this->createFileService($outputDirectory));
    }
}
