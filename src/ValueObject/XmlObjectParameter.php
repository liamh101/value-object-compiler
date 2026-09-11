<?php

namespace LiamH\ValueObjectCompiler\ValueObject;

readonly class XmlObjectParameter extends ObjectParameter
{
    public function __construct(
        string $originalName,
        string $formattedName,
        array $types,
        array $arrayTypes = [],
        public bool $isAttribute = false,
        ?DecodedObject $subObject = null
    ) {
        parent::__construct($originalName, $formattedName, $types, $arrayTypes, $subObject);
    }
}
