<?php

namespace Generator;

use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Generator\XmlGenerator;
use LiamH\ValueObjectCompiler\Service\NameService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\XmlObjectParameter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class XmlGeneratorTest extends TestCase
{
    /**
     * @dataProvider singleLevelObjectProvider
     */
    public function testGenerateSingleLevelObject(\SimpleXMLElement $data, DecodedObject $expectedObject): void
    {
        $reflection = new \ReflectionClass(XmlGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [$data]);

        self::assertSame($expectedObject->name, $result->name);
        self::assertSame($expectedObject->parameters['typeTest']->originalName, $result->parameters['typeTest']->originalName);
        self::assertSame($expectedObject->parameters['typeTest']->formattedName, $result->parameters['typeTest']->formattedName);
        self::assertSame($expectedObject->parameters['typeTest']->types, $result->parameters['typeTest']->types);
        self::assertSame($expectedObject->parameters['typeTest']->isAttribute, $result->parameters['typeTest']->isAttribute);
    }

    public static function singleLevelObjectProvider(): array
    {
        return [
            'string' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><typeTest>Hello!</typeTest></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::STRING])]
                )
            ],
            'string - attribute' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject typeTest="Hello!"></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::STRING], [], true)]
                )
            ],
            'integer' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><typeTest>1</typeTest></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::INTEGER])]
                )
            ],
            'integer - attribute' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject typeTest="1"></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::INTEGER], [], true)]
                )
            ],
            'float' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><typeTest>5.12</typeTest></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::FLOAT])]
                )
            ],
            'float- attribute' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject typeTest="5.12"></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::FLOAT], [], true)]
                )
            ],
            'boolean true' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><typeTest>true</typeTest></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN])]
                )
            ],
            'boolean false' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><typeTest>false</typeTest></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN])]
                )
            ],
            'boolean true - attribute' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject typeTest="true"></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN], [], true)]
                )
            ],
            'boolean false - attribute' => [
                simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject typeTest="false"></testObject>'),
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new XmlObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN], [], true)]
                )
            ],
        ];
    }


    /**
     * @dataProvider determineXmlTypeProvider
     */
    public function testDetermineXmlType(string $value, ParameterType $expectedType): void
    {
        $reflectionMethod = new ReflectionMethod(XmlGenerator::class, 'determineXmlType');
        self::assertSame($expectedType, $reflectionMethod->invoke($this->createGenerator(), $value));
    }

    public static function determineXmlTypeProvider(): array
    {
        return [
            'string' => ['Hello World', ParameterType::STRING],
            'integer' => ['10', ParameterType::INTEGER],
            'integer - minus' => ['-10', ParameterType::INTEGER],
            'float' => ['10.5', ParameterType::FLOAT],
            'float - Large Number' => [(string)(PHP_INT_MAX + 1), ParameterType::FLOAT],
            'boolean - true' => ['true', ParameterType::BOOLEAN],
            'boolean - false' => ['false', ParameterType::BOOLEAN],
            'null' => ['', ParameterType::NULL],
        ];
    }

    public function testBuildObjectParameter(): void
    {

    }

    public function testHandleArrayType(): void
    {

    }

    private function createGenerator(): XmlGenerator
    {
        return new XmlGenerator(new NameService());
    }

}