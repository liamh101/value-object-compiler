<?php

namespace Factory;

use LiamH\ValueObjectCompiler\Factory\JsonGeneratorCommandFactory;
use LiamH\ValueObjectCompiler\Generator\JsonGenerator;
use LiamH\ValueObjectCompiler\Generator\ValueObjectGenerator;
use PHPUnit\Framework\TestCase;

class JsonGeneratorCommandFactoryTest extends TestCase
{
    public function testFactoryGeneration(): void
    {
        $factory = new JsonGeneratorCommandFactory();

        self::assertInstanceOf(ValueObjectGenerator::class, $factory->createFileGenerator('./'));
        self::assertInstanceOf(JsonGenerator::class, $factory->createSourceGenerator());
    }

}