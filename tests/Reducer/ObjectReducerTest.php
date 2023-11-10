<?php

namespace Reducer;

use LiamH\ValueObjectCompiler\Enum\FileExtension;
use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Exception\ObjectReducerException;
use LiamH\ValueObjectCompiler\Reducer\ObjectReducer;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\GeneratedFile;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
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
        $this->expectException(ObjectReducerException::class);
        $this->expectExceptionMessage('Object reducer requires DecodedObject. LiamH\ValueObjectCompiler\ValueObject\GeneratedFile passed');

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

    public function testSetNonExistingParameterAsNullable(): void
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
        $method->invokeArgs($reducer, ['NonExistent']);

        $result = $property->getValue($reducer);

        self::assertFalse(isset($result['NonExistent']));
    }

    public function testReduceChildrenObjectsOnly(): void
    {
        $childArrayOne = new DecodedObject(
            'ChildArray',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING])]
        );
        $childArrayTwo = new DecodedObject(
            'ChildArray',
            [
                'String' => new ObjectParameter('String', 'String', [ParameterType::STRING]),
                'Int' => new ObjectParameter('Int', 'Int', [ParameterType::INTEGER])
            ]
        );

        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['ObjectArray' => new ObjectParameter('ObjectArray', 'ObjectArray', [ParameterType::ARRAY], [$childArrayOne, $childArrayTwo])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING, ParameterType::NULL])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertInstanceOf(ObjectParameter::class, $result->parameters['ObjectArray']);
        self::assertSame([ParameterType::ARRAY, ParameterType::NULL], $result->parameters['ObjectArray']->types);
        self::assertCount(1, $result->parameters['ObjectArray']->arrayTypes);
        self::assertInstanceOf(DecodedObject::class, $result->parameters['ObjectArray']->arrayTypes[0]);

        self::assertSame('ChildArray', $result->parameters['ObjectArray']->arrayTypes[0]->name);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->originalName);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->types);

        self::assertSame('Int', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['Int']->originalName);
        self::assertSame('Int', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['Int']->formattedName);
        self::assertSame([ParameterType::INTEGER, ParameterType::NULL], $result->parameters['ObjectArray']->arrayTypes[0]->parameters['Int']->types);
    }

    public function testReduceChildrenMixedTypes(): void
    {
        $childArrayOne = new DecodedObject(
            'ChildArray',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING])]
        );
        $childArrayTwo = new DecodedObject(
            'ChildArray',
            [
                'String' => new ObjectParameter('String', 'String', [ParameterType::STRING]),
                'Int' => new ObjectParameter('Int', 'Int', [ParameterType::INTEGER])
            ]
        );

        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['ObjectArray' => new ObjectParameter('ObjectArray', 'ObjectArray', [ParameterType::ARRAY], [$childArrayOne, $childArrayTwo, ParameterType::INTEGER])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING, ParameterType::NULL])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertInstanceOf(ObjectParameter::class, $result->parameters['ObjectArray']);
        self::assertSame([ParameterType::ARRAY, ParameterType::NULL], $result->parameters['ObjectArray']->types);
        self::assertCount(2, $result->parameters['ObjectArray']->arrayTypes);
        self::assertSame(ParameterType::INTEGER, $result->parameters['ObjectArray']->arrayTypes[0]);
        self::assertInstanceOf(DecodedObject::class, $result->parameters['ObjectArray']->arrayTypes[1]);

        self::assertSame('ChildArray', $result->parameters['ObjectArray']->arrayTypes[1]->name);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[1]->parameters['String']->originalName);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[1]->parameters['String']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['ObjectArray']->arrayTypes[1]->parameters['String']->types);

        self::assertSame('Int', $result->parameters['ObjectArray']->arrayTypes[1]->parameters['Int']->originalName);
        self::assertSame('Int', $result->parameters['ObjectArray']->arrayTypes[1]->parameters['Int']->formattedName);
        self::assertSame([ParameterType::INTEGER, ParameterType::NULL], $result->parameters['ObjectArray']->arrayTypes[1]->parameters['Int']->types);

    }

    public function testReduceChildrenBelowMinimum(): void
    {
        $childArrayOne = new DecodedObject(
            'ChildArray',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING])]
        );

        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['ObjectArray' => new ObjectParameter('ObjectArray', 'ObjectArray', [ParameterType::ARRAY], [$childArrayOne, ParameterType::INTEGER])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['String' => new ObjectParameter('String', 'String', [ParameterType::STRING, ParameterType::NULL])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertInstanceOf(ObjectParameter::class, $result->parameters['ObjectArray']);
        self::assertSame([ParameterType::ARRAY, ParameterType::NULL], $result->parameters['ObjectArray']->types);
        self::assertCount(2, $result->parameters['ObjectArray']->arrayTypes);
        self::assertSame(ParameterType::INTEGER, $result->parameters['ObjectArray']->arrayTypes[1]);
        self::assertInstanceOf(DecodedObject::class, $result->parameters['ObjectArray']->arrayTypes[0]);

        self::assertSame('ChildArray', $result->parameters['ObjectArray']->arrayTypes[0]->name);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->originalName);
        self::assertSame('String', $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->formattedName);
        self::assertSame([ParameterType::STRING], $result->parameters['ObjectArray']->arrayTypes[0]->parameters['String']->types);
    }

    public function testUpdateExistingParameterStandard(): void
    {
        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['Mixed' => new ObjectParameter('Mixed', 'Mixed', [ParameterType::STRING, ParameterType::INTEGER])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['Mixed' => new ObjectParameter('Mixed', 'Mixed', [ParameterType::STRING])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertInstanceOf(ObjectParameter::class, $result->parameters['Mixed']);
        self::assertSame('Mixed', $result->parameters['Mixed']->originalName);
        self::assertSame('Mixed', $result->parameters['Mixed']->formattedName);
        self::assertSame([ParameterType::STRING, ParameterType::INTEGER], $result->parameters['Mixed']->types);
    }

    public function testUpdateExistingParameterArray(): void
    {
        $decodedObjectOne = new DecodedObject(
            'Hello World',
            ['Mixed' => new ObjectParameter('Mixed', 'Mixed', [ParameterType::ARRAY], [ParameterType::STRING])]
        );
        $decodedObjectTwo = new DecodedObject(
            'Hello World',
            ['Mixed' => new ObjectParameter('Mixed', 'Mixed', [ParameterType::ARRAY], [ParameterType::STRING, ParameterType::INTEGER])]
        );
        $decodedObjectArray = [$decodedObjectOne, $decodedObjectTwo];

        $reducer = new ObjectReducer($decodedObjectArray);
        $result = $reducer->reduceObjects();

        self::assertInstanceOf(ObjectParameter::class, $result->parameters['Mixed']);
        self::assertSame('Mixed', $result->parameters['Mixed']->originalName);
        self::assertSame('Mixed', $result->parameters['Mixed']->formattedName);
        self::assertSame([ParameterType::ARRAY], $result->parameters['Mixed']->types);
        self::assertSame([ParameterType::STRING, ParameterType::INTEGER], $result->parameters['Mixed']->arrayTypes);
    }
}