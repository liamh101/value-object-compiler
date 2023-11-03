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

    public function createFiles(DecodedObject $baseObject): true
    {
        $generatedFiles = [];
        $this->addObject($baseObject);

        foreach ($this->availableObjects as $object) {
            $generatedFiles[] = new GeneratedFile(
                $object->name,
                $this->fileService->populateValueObjectFile(
                    $object->name,
                    $this->decodedObjectService->generateDocblock($object),
                    $this->decodedObjectService->generateParameters($object),
                    $this->decodedObjectService->generateHydrationValidation($object),
                    $this->decodedObjectService->generateHydrationLogic($object),
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
