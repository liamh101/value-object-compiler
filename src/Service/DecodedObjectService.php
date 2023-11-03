<?php

namespace LiamH\ValueObjectCompiler\Service;

use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;

interface DecodedObjectService
{
    public function generateParameters(DecodedObject $decodedObject): string;
    public function generateHydrationValidation(DecodedObject $decodedObject): string;
    public function generateHydrationLogic(DecodedObject $decodedObject): string;
    public function generateDocblock(DecodedObject $decodedObject): string;
}
