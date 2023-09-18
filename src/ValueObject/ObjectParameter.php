<?php

namespace LiamH\Valueobjectgenerator\ValueObject;

use LiamH\Valueobjectgenerator\Enum\ParameterType;

readonly class ObjectParameter
{
    /**
     * @param ParameterType[] $types
     */
    public function __construct(
        public string $originalName,
        public string $formattedName,
        public array $types,
        public ?DecodedObject $subObject = null,
        public ?array $arrayTypes = [],
    ) {
    }

    public function hasType(ParameterType $type): bool
    {
        return in_array($type, $this->types, true);
    }
}