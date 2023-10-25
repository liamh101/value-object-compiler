<?php

namespace Reducer;

use LiamH\Valueobjectgenerator\Enum\FileExtension;
use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\Exception\ObjectReducerException;
use LiamH\Valueobjectgenerator\Reducer\ObjectReducer;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\GeneratedFile;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;
use PHPUnit\Framework\TestCase;

class ObjectReducerTest extends TestCase
{

    public function testBelowMinimumObjects(): void
    {
        self::expectException(ObjectReducerException::class);
        self::expectExceptionMessage('Not enough Objects to reduce');

        $decodedObjectArray = [new DecodedObject('Hello World', [])];

        new ObjectReducer($decodedObjectArray);
    }

    public function testPassInvalidIntType(): void
    {
        self::expectException(ObjectReducerException::class);
        self::expectExceptionMessage('Object reducer requires DecodedObject. integer passed');

        $decodedObjectArray = [new DecodedObject('Hello World', []), 1];

        new ObjectReducer($decodedObjectArray);
    }

    public function testPassInvalidObjectType(): void
    {
        self::expectException(ObjectReducerException::class);
        self::expectExceptionMessage('Object reducer requires DecodedObject. LiamH\Valueobjectgenerator\ValueObject\GeneratedFile passed');

        $decodedObjectArray = [new DecodedObject('Hello World', []), new GeneratedFile('Test', 'hello', FileExtension::PHP)];

        new ObjectReducer($decodedObjectArray);
    }

    public function testReduceChildArrays(): void
    {
        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['Object' => new ObjectParameter('Object', 'Object', [ParameterType::OBJECT], [], new DecodedObject('Child', ['childParameter' => new ObjectParameter('childParameter', 'childParameter', [ParameterType::STRING])]))]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['Object' => new ObjectParameter('Object', 'Object', [ParameterType::OBJECT], [], new DecodedObject('Child', ['childParameter' => new ObjectParameter('childParameter', 'childParameter', [ParameterType::INTEGER])]))]
        );

        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertSame('Hello World', $result->name);
        self::assertSame('Object', $result->parameters['Object']->originalName);
        self::assertSame('Object', $result->parameters['Object']->formattedName);
        self::assertSame([ParameterType::OBJECT], $result->parameters['Object']->types);
        self::assertSame('Child', $result->parameters['Object']->subObject->name);
        self::assertSame('childParameter', $result->parameters['Object']->subObject->parameters['childParameter']->originalName);
        self::assertSame('childParameter', $result->parameters['Object']->subObject->parameters['childParameter']->formattedName);
        self::assertSame([ParameterType::STRING, ParameterType::INTEGER], $result->parameters['Object']->subObject->parameters['childParameter']->types);
    }

    public function testSetParameterAsNullable(): void
    {
        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING]), 'Int' => new ObjectParameter('Int', 'Int', [ParameterType::INTEGER])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertSame('Hello World', $result->name);
        self::assertSame('String', $result->parameters['String']->originalName);
        self::assertSame('String', $result->parameters['String']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['String']->types);

        self::assertSame('Int', $result->parameters['Int']->originalName);
        self::assertSame('Int', $result->parameters['Int']->formattedName);
        self::assertSame([ParameterType::INTEGER, ParameterType::NULL], $result->parameters['Int']->types);
    }

    public function testSetAlreadyNullParameterAsNullable(): void
    {
        $reflection = new \ReflectionClass(ObjectReducer::class);
        $property = $reflection->getProperty('masterParameters');
        $property->setAccessible(true);
        $method = $reflection->getMethod('setParameterAsNullable');
        $method->setAccessible(true);

        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING, ParameterType::NULL])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $method->invokeArgs($reducer, ['String']);

        $result = $property->getValue($reducer);

        self::assertSame([ParameterType::STRING, ParameterType::NULL], $result['String']->types);
    }
}