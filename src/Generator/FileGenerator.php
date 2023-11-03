<?php

namespace LiamH\ValueObjectCompiler\Generator;

use LiamH\ValueObjectCompiler\Service\DecodedObjectService;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;

interface FileGenerator
{
    public function __construct(DecodedObjectService $decodedObjectService, FileService $fileService);
    public function createFiles(DecodedObject $baseObject): true;
}
