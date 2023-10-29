<?php

namespace LiamH\Valueobjectgenerator\Generator;

use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;

interface FileGenerator
{
    public function __construct(DecodedObjectService $decodedObjectService, FileService $fileService);
    public function createFiles(DecodedObject $baseObject): true;
}
