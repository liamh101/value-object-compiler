<?php

namespace Generator;

use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Generator\JsonGenerator;
use LiamH\ValueObjectCompiler\Service\NameService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
use PHPUnit\Framework\TestCase;

class JsonGeneratorTest extends TestCase
{
    public function testIsSubClassValid(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('isSubclass');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [['name' => 'Object', 'type' => 'Subclass']]);

        self::assertTrue($result);
    }

    public function testIsSubClassInvalid(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('isSubclass');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [['Not a subobject', 'just an array']]);

        self::assertFalse($result);
    }

    public function testHandleArrayTypeString(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('handleArrayType');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [['Hello World', 'test'], 'string test', 'StringTest']);

        self::assertInstanceOf(ObjectParameter::class, $result);
        self::assertSame('string test', $result->originalName);
        self::assertSame('StringTest', $result->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->types);
        self::assertSame([ParameterType::STRING], $result->arrayTypes);
        self::assertNull($result->subObject);
    }

    public function testHandleArrayTypeMixedTypes(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('handleArrayType');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [['Hello World', 18, 13, 29.4, 10.8, true, false], 'string test', 'StringTest']);

        self::assertInstanceOf(ObjectParameter::class, $result);
        self::assertSame('string test', $result->originalName);
        self::assertSame('StringTest', $result->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->types);
        self::assertSame([ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN], $result->arrayTypes);
        self::assertNull($result->subObject);
    }

    public function testHandleArrayTypeObject(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('handleArrayType');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [[['name' => 'Hello World', 'type' => 'object']], 'object test', 'ObjectTest']);

        self::assertInstanceOf(ObjectParameter::class, $result);
        self::assertSame('object test', $result->originalName);
        self::assertSame('ObjectTest', $result->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->types);
        self::assertCount(1, $result->arrayTypes);
        self::assertNull($result->subObject);

        $expectedSubParameterName = new ObjectParameter('name', 'name', [ParameterType::STRING], [], null);
        $expectedSubParameterType = new ObjectParameter('type', 'type', [ParameterType::STRING], [], null);

        $expectedObject = new DecodedObject('ObjectTes', [$expectedSubParameterName, $expectedSubParameterType]);

        self::assertInstanceOf(DecodedObject::class, $result->arrayTypes[0]);
        self::assertSame('ObjectTest', $result->arrayTypes[0]->name);
        self::assertCount(2, $result->arrayTypes[0]->parameters);

        self::assertSame('name', $result->arrayTypes[0]->parameters['name']->originalName);
        self::assertSame('name', $result->arrayTypes[0]->parameters['name']->formattedName);
        self::assertSame([ParameterType::STRING], $result->arrayTypes[0]->parameters['name']->types);
        self::assertSame([], $result->arrayTypes[0]->parameters['name']->arrayTypes);
        self::assertNull($result->arrayTypes[0]->parameters['name']->subObject);

        self::assertSame('type', $result->arrayTypes[0]->parameters['type']->originalName);
        self::assertSame('type', $result->arrayTypes[0]->parameters['type']->formattedName);
        self::assertSame([ParameterType::STRING], $result->arrayTypes[0]->parameters['type']->types);
        self::assertSame([], $result->arrayTypes[0]->parameters['type']->arrayTypes);
        self::assertNull($result->arrayTypes[0]->parameters['type']->subObject);
    }

    public function testHandleArrayTypeMultipleObject(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('handleArrayType');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [[['name' => 'Hello World', 'type' => 'object'], ['name' => 'Hello World']], 'object test', 'ObjectTest']);

        self::assertInstanceOf(ObjectParameter::class, $result);
        self::assertSame('object test', $result->originalName);
        self::assertSame('ObjectTest', $result->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->types);
        self::assertCount(1, $result->arrayTypes);
        self::assertNull($result->subObject);

        $expectedSubParameterName = new ObjectParameter('name', 'name', [ParameterType::STRING], [], null);
        $expectedSubParameterType = new ObjectParameter('type', 'type', [ParameterType::STRING], [], null);

        $expectedObject = new DecodedObject('ObjectTes', [$expectedSubParameterName, $expectedSubParameterType]);

        self::assertInstanceOf(DecodedObject::class, $result->arrayTypes[0]);
        self::assertSame('ObjectTest', $result->arrayTypes[0]->name);
        self::assertCount(2, $result->arrayTypes[0]->parameters);

        self::assertSame('name', $result->arrayTypes[0]->parameters['name']->originalName);
        self::assertSame('name', $result->arrayTypes[0]->parameters['name']->formattedName);
        self::assertSame([ParameterType::STRING], $result->arrayTypes[0]->parameters['name']->types);
        self::assertSame([], $result->arrayTypes[0]->parameters['name']->arrayTypes);
        self::assertNull($result->arrayTypes[0]->parameters['name']->subObject);

        self::assertSame('type', $result->arrayTypes[0]->parameters['type']->originalName);
        self::assertSame('type', $result->arrayTypes[0]->parameters['type']->formattedName);
        self::assertSame([ParameterType::STRING, ParameterType::NULL], $result->arrayTypes[0]->parameters['type']->types);
        self::assertSame([], $result->arrayTypes[0]->parameters['type']->arrayTypes);
        self::assertNull($result->arrayTypes[0]->parameters['type']->subObject);
    }

    public function testHandleArrayTypeArrayOfArrays(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('handleArrayType');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, [[['Hello World', 'test']], 'string test', 'StringTest']);

        self::assertInstanceOf(ObjectParameter::class, $result);
        self::assertSame('string test', $result->originalName);
        self::assertSame('StringTest', $result->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->types);
        self::assertSame([ParameterType::ARRAY], $result->arrayTypes);
        self::assertNull($result->subObject);
    }

    /**
     * @dataProvider singleLevelObjectProvider
     */
    public function testGenerateSingleLevelObject(array $data, DecodedObject $expectedObject): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, ['testObject', $data]);

        self::assertSame($expectedObject->name, $result->name);
        self::assertSame($expectedObject->parameters['typeTest']->originalName, $result->parameters['typeTest']->originalName);
        self::assertSame($expectedObject->parameters['typeTest']->formattedName, $result->parameters['typeTest']->formattedName);
        self::assertSame($expectedObject->parameters['typeTest']->types, $result->parameters['typeTest']->types);
    }

    public static function singleLevelObjectProvider(): array
    {
        return [
            'string' => [
                ['typeTest' => 'Hello World'],
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new ObjectParameter('typeTest', 'typeTest', [ParameterType::STRING])]
                )
            ],
            'integer' => [
                ['typeTest' => 1],
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new ObjectParameter('typeTest', 'typeTest', [ParameterType::INTEGER])]
                )
            ],
            'float' => [
                ['typeTest' => 5.32],
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new ObjectParameter('typeTest', 'typeTest', [ParameterType::FLOAT])]
                )
            ],
            'boolean true' => [
                ['typeTest' => true],
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new ObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN])]
                )
            ],
            'boolean false' => [
                ['typeTest' => false],
                new DecodedObject(
                    'TestObject',
                    ['typeTest' => new ObjectParameter('typeTest', 'typeTest', [ParameterType::BOOLEAN])]
                )
            ],
        ];
    }

    public function testGenerateObjectWithSubObject(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, ['testObject', ['objectType' => ['subValue' => 'Hello world']]]);

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
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, ['testObject', ['objectType' => ['subArray' => ['Hello world', 'This is a test']]]]);

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

    public function testGenerateObjectWithArrayOfObjects(): void
    {
        $reflection = new \ReflectionClass(JsonGenerator::class);
        $method = $reflection->getMethod('generateObject');
        $method->setAccessible(true);

        $generator = $this->createGenerator();

        $result = $method->invokeArgs($generator, ['testObject', ['objectType' => ['subArray' => [['required' => 'content', 'optionalOne' => 1], ['required' => 'more Content', 'optionalOne' => 5, 'optionalTwo' => 25.6], ['required' => 'final']]]]]);

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

    public function testGenerateClassFromSourceValidJson(): void
    {
        $generator = $this->createGenerator();

        $result = $generator->generateClassFromSource('ValidJson', '{"Name": "Valid JSON"}');
        self::assertSame('ValidJson', $result->name);
        self::assertCount(1, $result->parameters);
        self::assertSame('name', $result->parameters['name']->formattedName);
        self::assertSame('Name', $result->parameters['name']->originalName);
        self::assertSame([ParameterType::STRING], $result->parameters['name']->types);
    }

    public function testGenerateClassFromSourceValidArrayJson(): void
    {
        $generator = $this->createGenerator();

        $result = $generator->generateClassFromSource('ValidArrayJson', '[{"Name": "Valid JSON"}, {"Name": "Valid JSON 2", "Type": "Additional"}]');
        self::assertSame('ValidArrayJson', $result->name);
        self::assertCount(2, $result->parameters);

        self::assertSame('name', $result->parameters['name']->formattedName);
        self::assertSame('Name', $result->parameters['name']->originalName);
        self::assertSame([ParameterType::STRING], $result->parameters['name']->types);

        self::assertSame('type', $result->parameters['type']->formattedName);
        self::assertSame('Type', $result->parameters['type']->originalName);
        self::assertSame([ParameterType::STRING, ParameterType::NULL], $result->parameters['type']->types);
    }

    public function testGenerateClassFromSourceInvalidJson(): void
    {
        $generator = $this->createGenerator();

        $this->expectException(\JsonException::class);
        $result = $generator->generateClassFromSource('ValidJson', '{0: "Valid JSON"}');
    }

    private function createGenerator(): JsonGenerator
    {
        return new JsonGenerator(new NameService());
    }
}