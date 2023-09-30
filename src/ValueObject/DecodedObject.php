<?php

namespace LiamH\Valueobjectgenerator\ValueObject;

use LiamH\Valueobjectgenerator\Enum\ParameterType;

readonly class DecodedObject
{

    /**
     * @param string $name
     * @param ObjectParameter[] $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters,
    ) {
    }

    /**
     * @return DecodedObject[]
     */
    public function getChildObjects(): array
    {
        $objects = [];

        foreach ($this->parameters as $parameter) {
            if ($parameter->hasObject()) {
                $objects = [...$objects, ...$parameter->getObjects()];
            }
        }

        return $objects;
    }

    public function generateDocblock(): string
    {
        $hasDocblock = false;
        $docblock = '**';

        foreach ($this->parameters as $parameter) {
            if (is_array($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= '\n@var ';

                foreach ($parameter->arrayTypes as $key => $type) {
                    if ($key > 0) {
                        $docblock .= '|';
                    }

                    if ($type instanceof self) {
                        $docblock .= $type->name . '[]';
                        continue;
                    }

                    if ($type instanceof ParameterType) {
                        $docblock .= $type->getDefinitionName() . '[]';
                    }
                }

                $docblock .= ' $' . $parameter->formattedName;
            }
        }

        if (!$hasDocblock) {
            return '';
        }

        $docblock .= '/n**';
        return $docblock;
    }
}