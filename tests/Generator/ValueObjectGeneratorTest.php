<?php

namespace Generator;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;
use PHPUnit\Framework\TestCase;

class ValueObjectGeneratorTest extends TestCase
{
    public function testGenerateSingleLevelObject(): void
    {
        $object = new DecodedObject('HelloWorld', [new ObjectParameter('string', 'string', [ParameterType::STRING])]);

        $objectService = $this->createMock(DecodedObjectService::class);
        $objectService->expects($this->once())->method('generateDocblock')->with($object)->willReturn('Docblock');
        $objectService->expects($this->once())->method('generateParameters')->with($object)->willReturn('Parameter');
        $objectService->expects($this->once())->method('generateHydrationLogic')->with($object)->willReturn('Hydration');

        $fileService = $this->createMock(FileService::class);
        $fileService->expects($this->once())
            ->method('populateValueObjectFile')
            ->with('HelloWorld', 'Docblock', 'Parameter', 'Hydration')
            ->willReturn('Successful File');

        $fileService->expects($this->once())
            ->method('writeFile')
            ->with($this->anything())
            ->willReturn(true);

        $service = new ValueObjectGenerator($objectService, $fileService);
        self::assertTrue($service->createFiles($object));
    }

    public function testGenerateMultiLevelObjectWithArray(): void
    {
        $subObject = new DecodedObject('SubObject', [new ObjectParameter('string', 'string', [ParameterType::STRING])]);
        $object = new DecodedObject(
            'HelloWorld',
            [
                new ObjectParameter('string', 'string', [ParameterType::STRING]),
                new ObjectParameter('array', 'array', [ParameterType::ARRAY], [$subObject]),
            ]);

        $objectService = $this->createMock(DecodedObjectService::class);
        $objectService->expects($this->exactly(2))
            ->method('generateDocblock')
            ->willReturn('Docblock');
        $objectService->expects($this->exactly(2))->method('generateParameters')->willReturn('Parameter');
        $objectService->expects($this->exactly(2))->method('generateHydrationLogic')->willReturn('Hydration');

        $fileService = $this->createMock(FileService::class);
        $fileService->expects($this->exactly(2))
            ->method('populateValueObjectFile')
            ->willReturn('Successful File');

        $fileService->expects($this->exactly(2))
            ->method('writeFile')
            ->with($this->anything())
            ->willReturn(true);

        $service = new ValueObjectGenerator($objectService, $fileService);
        self::assertTrue($service->createFiles($object));
    }

    public function testGenerateMultiLevelObjectWithParameter(): void
    {
        $subObject = new DecodedObject('SubObject', [new ObjectParameter('string', 'string', [ParameterType::STRING])]);
        $object = new DecodedObject(
            'HelloWorld',
            [
                new ObjectParameter('string', 'string', [ParameterType::STRING]),
                new ObjectParameter('object', 'object', [ParameterType::OBJECT], [], $subObject),
            ]
        );

        $objectService = $this->createMock(DecodedObjectService::class);
        $objectService->expects($this->exactly(2))
            ->method('generateDocblock')
            ->willReturn('Docblock');
        $objectService->expects($this->exactly(2))->method('generateParameters')->willReturn('Parameter');
        $objectService->expects($this->exactly(2))->method('generateHydrationLogic')->willReturn('Hydration');

        $fileService = $this->createMock(FileService::class);
        $fileService->expects($this->exactly(2))
            ->method('populateValueObjectFile')
            ->willReturn('Successful File');

        $fileService->expects($this->exactly(2))
            ->method('writeFile')
            ->with($this->anything())
            ->willReturn(true);

        $service = new ValueObjectGenerator($objectService, $fileService);
        self::assertTrue($service->createFiles($object));
    }
}