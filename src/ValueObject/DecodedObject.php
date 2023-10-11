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

    /**
     * @return ObjectParameter[]
     */
    public function getRequiredParameters(): array
    {
        $requiredParameters = array_filter($this->parameters, static fn (ObjectParameter $objectParameter) => !$objectParameter->hasType(ParameterType::NULL));
        ksort($requiredParameters);

        return $requiredParameters;
    }

    /**
     * @return ObjectParameter[]
     */
    public function getOptionalParameters(): array
    {
        $optionalParameters = array_filter($this->parameters, static fn (ObjectParameter $objectParameter) => $objectParameter->hasType(ParameterType::NULL));
        ksort($optionalParameters);

        return $optionalParameters;
    }
}
