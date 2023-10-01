<?php

namespace LiamH\Valueobjectgenerator\ValueObject;

use LiamH\Valueobjectgenerator\Enum\ParameterType;

readonly class ObjectParameter
{
    /**
     * @param ParameterType[] $types
     * @param ParameterType[]|DecodedObject[] $arrayTypes
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

    public function hasObject(): bool
    {
        if ($this->subObject && $this->hasType(ParameterType::OBJECT)) {
            return true;
        }

        if ($this->hasType(ParameterType::ARRAY)) {
            foreach ($this->arrayTypes as $arrayType) {
                if ($arrayType instanceof DecodedObject) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return DecodedObject[]
     */
    public function getObjects(): array
    {
        $objects = [];

        if (!$this->hasObject()) {
            return $objects;
        }

        if ($this->subObject && $this->hasType(ParameterType::OBJECT)) {
            $objects[] = $this->subObject;
        }

        if ($this->hasType(ParameterType::ARRAY)) {
            foreach ($this->arrayTypes as $arrayType) {
                if ($arrayType instanceof DecodedObject) {
                    $objects[] = $arrayType;
                }
            }
        }

        return $objects;
    }
}
