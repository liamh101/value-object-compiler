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

    public function testGenerateObjectWithSubObject(): void
    {
        $reflection = new \ReflectionClass(XmlGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><objectType><subValue>Hello world</subValue></objectType></testObject>')]);

        self::assertSame('TestObject', $result->name);
        self::assertSame('objectType', $result->parameters['objectType']->originalName);
        self::assertSame('objectType', $result->parameters['objectType']->formattedName);
        self::assertSame([ParameterType::OBJECT], $result->parameters['objectType']->types);
        self::assertSame('ObjectType', $result->parameters['objectType']->subObject->name);
        self::assertSame('subValue', $result->parameters['objectType']->subObject->parameters['subValue']->originalName);
        self::assertSame('subValue', $result->parameters['objectType']->subObject->parameters['subValue']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['objectType']->subObject->parameters['subValue']->types);
    }

    public function testGenerateObjectWithArray(): void
    {
        $reflection = new \ReflectionClass(XmlGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><objectType><subArray>Hello world</subArray><subArray>Foobar</subArray></objectType></testObject>')]);

        self::assertSame('TestObject', $result->name);
        self::assertSame('objectType', $result->parameters['objectType']->originalName);
        self::assertSame('objectType', $result->parameters['objectType']->formattedName);
        self::assertSame([ParameterType::OBJECT], $result->parameters['objectType']->types);

        self::assertSame('ObjectType', $result->parameters['objectType']->subObject->name);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->originalName);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->parameters['objectType']->subObject->parameters['subArray']->types);
        self::assertSame([ParameterType::STRING], $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes);
    }

    public function testGenerateObjectWithMultiTypeArray(): void
    {
        $reflection = new \ReflectionClass(XmlGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><objectType><subArray>Hello world</subArray><subArray>1</subArray></objectType></testObject>')]);

        self::assertSame('TestObject', $result->name);
        self::assertSame('objectType', $result->parameters['objectType']->originalName);
        self::assertSame('objectType', $result->parameters['objectType']->formattedName);
        self::assertSame([ParameterType::OBJECT], $result->parameters['objectType']->types);

        self::assertSame('ObjectType', $result->parameters['objectType']->subObject->name);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->originalName);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->parameters['objectType']->subObject->parameters['subArray']->types);
        self::assertSame([ParameterType::STRING, ParameterType::INTEGER], $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes);
    }

    public function testGenerateObjectWithArrayOfObjects(): void
    {
        $reflection = new \ReflectionClass(XmlGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><testObject><objectType><subArray><required>Hello</required><optionalOne>1</optionalOne></subArray><subArray><required>World</required><optionalOne>2</optionalOne><optionalTwo>1.5</optionalTwo></subArray><subArray><required>Final</required></subArray></objectType></testObject>')]);

        self::assertSame('TestObject', $result->name);
        self::assertSame('objectType', $result->parameters['objectType']->originalName);
        self::assertSame('objectType', $result->parameters['objectType']->formattedName);
        self::assertSame([ParameterType::OBJECT], $result->parameters['objectType']->types);

        self::assertSame('ObjectType', $result->parameters['objectType']->subObject->name);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->originalName);
        self::assertSame('subArray', $result->parameters['objectType']->subObject->parameters['subArray']->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->parameters['objectType']->subObject->parameters['subArray']->types);

        self::assertInstanceOf(DecodedObject::class, $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]);
        self::assertSame('SubArray', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->name);
        self::assertCount(3, $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters);

        self::assertSame('required', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['required']->originalName);
        self::assertSame('required', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['required']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['required']->types);

        self::assertSame('optionalOne', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalOne']->originalName);
        self::assertSame('optionalOne', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalOne']->formattedName);
        self::assertSame([ParameterType::INTEGER, ParameterType::NULL], $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalOne']->types);

        self::assertSame('optionalTwo', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalTwo']->originalName);
        self::assertSame('optionalTwo', $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalTwo']->formattedName);
        self::assertSame([ParameterType::FLOAT, ParameterType::NULL], $result->parameters['objectType']->subObject->parameters['subArray']->arrayTypes[0]->parameters['optionalTwo']->types);
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

    public function testGenerateClassFromSourceValidXml(): void
    {
        $generator = $this->createGenerator();

        $result = $generator->generateClassFromSource('ValidXml', '<?xml version="1.0" encoding="UTF-8"?><testObject><Name>Hello World</Name></testObject>');
        self::assertSame('TestObject', $result->name);
        self::assertCount(1, $result->parameters);

        self::assertSame('name', $result->parameters['name']->formattedName);
        self::assertSame('Name', $result->parameters['name']->originalName);
        self::assertSame([ParameterType::STRING], $result->parameters['name']->types);
    }

    public function testGenerateClassFromSourceInvalidXml(): void
    {
        libxml_use_internal_errors(true);
        $generator = $this->createGenerator();

        $this->expectException(\RuntimeException::class);
        $generator->generateClassFromSource('InvalidXml', '<?xml version="1.0" encoding="UTF-8"?><testObject><Name>Hello World<Name></testObject>');
        libxml_use_internal_errors(false);
    }

    private function createGenerator(): XmlGenerator
    {
        return new XmlGenerator(new NameService());
    }

}