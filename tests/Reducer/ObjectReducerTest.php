<?php

namespace Reducer;

use LiamH\Valueobjectgenerator\Enum\FileExtension;
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
}