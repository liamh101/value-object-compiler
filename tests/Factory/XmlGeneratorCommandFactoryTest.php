<?php

namespace Factory;

use LiamH\ValueObjectCompiler\Factory\XmlGeneratorCommandFactory;
use LiamH\ValueObjectCompiler\Generator\JsonGenerator;
use LiamH\ValueObjectCompiler\Generator\ValueObjectGenerator;
use LiamH\ValueObjectCompiler\Generator\XmlGenerator;
use PHPUnit\Framework\TestCase;

class XmlGeneratorCommandFactoryTest extends TestCase
{
    public function testFactoryGeneration(): void
    {
        $factory = new XmlGeneratorCommandFactory();

        self::assertInstanceOf(ValueObjectGenerator::class, $factory->createFileGenerator('./'));
        self::assertInstanceOf(XmlGenerator::class, $factory->createSourceGenerator());
    }
}