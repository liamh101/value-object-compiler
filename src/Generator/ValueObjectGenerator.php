<?php

namespace LiamH\ValueObjectCompiler\Generator;

use LiamH\ValueObjectCompiler\Enum\FileExtension;
use LiamH\ValueObjectCompiler\Service\DecodedObjectService;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\GeneratedFile;

class ValueObjectGenerator implements FileGenerator
{
    /** @var DecodedObject[] */
    private array $availableObjects = [];

    public function __construct(
        private readonly DecodedObjectService $decodedObjectService,
        private readonly FileService $fileService,
    ) {
    }

    public function createFiles(DecodedObject $baseObject, bool $addSource = false): true
    {
        $generatedFiles = [];
        $this->addObject($baseObject);

        foreach ($this->availableObjects as $object) {
            $generatedFiles[] = new GeneratedFile(
                $object->name,
                $this->fileService->populateValueObjectFile(
                    className: $object->name,
                    docblock: $this->decodedObjectService->generateDocblock($object),
                    parameters: $this->decodedObjectService->generateParameters($object),
                    hydrationValidation: $this->decodedObjectService->generateHydrationValidation($object),
                    hydrationLogic: $this->decodedObjectService->generateHydrationLogic($object),
                    hydrationParameter: $this->decodedObjectService->getHydrationParameter()->value,
                    toSourceDefinition: $addSource ? $this->decodedObjectService->getToSourceDefinition($object) : '',
                    toSourceLogic: $addSource ? $this->decodedObjectService->getToSourceLogic($object) : '',
                ),
                FileExtension::PHP
            );
        }

        array_walk($generatedFiles, fn (GeneratedFile $file) => $this->fileService->writeFile($file));

        return true;
    }

    private function addObject(DecodedObject $decodedObject): void
    {
        if (!isset($this->availableObjects[$decodedObject->name])) {
            $this->availableObjects[$decodedObject->name] = $decodedObject;
        }

        foreach ($decodedObject->getChildObjects() as $child) {
            $this->addObject($child);
        }
    }
}
