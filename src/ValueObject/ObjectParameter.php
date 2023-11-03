<?php

namespace LiamH\ValueObjectCompiler\ValueObject;

use LiamH\ValueObjectCompiler\Enum\ParameterType;

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
        public array $arrayTypes = [],
        public ?DecodedObject $subObject = null,
    ) {
    }

    public function hasType(ParameterType $type): bool
    {
        return in_array($type, $this->types, true);
    }

    public function hasArrayType(ParameterType $type): bool
    {
        return in_array($type, $this->arrayTypes, true);
    }

    public function hasObject(): bool
    {
        if ($this->subObject && $this->hasType(ParameterType::OBJECT)) {
            return true;
        }

        if (is_array($this->arrayTypes) && $this->hasType(ParameterType::ARRAY)) {
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

        if (is_array($this->arrayTypes) && $this->hasType(ParameterType::ARRAY)) {
            foreach ($this->arrayTypes as $arrayType) {
                if ($arrayType instanceof DecodedObject) {
                    $objects[] = $arrayType;
                }
            }
        }

        return $objects;
    }
}
