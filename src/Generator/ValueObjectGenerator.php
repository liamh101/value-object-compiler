<?php

namespace LiamH\Valueobjectgenerator\Generator;

use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;

class ValueObjectGenerator
{
    private array $availableObjects = [];

    public function __construct(
        private readonly FileService $fileService,
    ) {
    }

    public function createFiles(DecodedObject $baseObject): bool
    {
        $generatedFiles = [];
        $this->addObject($baseObject);

        foreach ($this->availableObjects as $object) {
            $generatedFiles[] = $this->fileService->populateValueObjectFile($object);
        }

        die(var_dump($generatedFiles));
    }

    public function addObject(DecodedObject $decodedObject): void
    {
        if (!isset($this->availableObjects[$decodedObject->name])) {
            $this->availableObjects[$decodedObject->name] = $decodedObject;
        }

        foreach ($decodedObject->getChildObjects() as $child) {
           $this->addObject($child);
        }
    }
}