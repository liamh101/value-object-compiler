<?php

namespace Factory;

use LiamH\Valueobjectgenerator\Factory\JsonGeneratorCommandFactory;
use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use PHPUnit\Framework\TestCase;

class JsonGeneratorCommandFactoryTest extends TestCase
{
    public function testFactoryGeneration(): void
    {
        $factory = new JsonGeneratorCommandFactory();

        self::assertInstanceOf(ValueObjectGenerator::class, $factory->createFileGenerator());
        self::assertInstanceOf(JsonGenerator::class, $factory->createSourceGenerator());
    }

}