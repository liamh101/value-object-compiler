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

    public function generateParameters(): string
    {
        $parameters = '';

        foreach ($this->getRequiredParameters() as $requiredParameter) {
            $parameters .= 'public ';

            foreach ($requiredParameter->types as $key => $type) {
                if ($key > 0) {
                    $parameters .= '|';
                }

                if ($type === ParameterType::OBJECT) {
                    $parameters .= $requiredParameter->subObject->name;
                }

                $parameters .= $type->getDefinitionName();
            }

            $parameters .= ' $' . $requiredParameter->formattedName . ',' . PHP_EOL;
        }

        foreach ($this->getOptionalParameters() as $optionalParameter) {
            $parameters .= 'public ';
            $multipleTypes = false;
            $hasArray = false;

            foreach ($optionalParameter->types as $type) {
                if ($type === ParameterType::NULL) {
                    continue;
                }

                if ($multipleTypes) {
                    $parameters .= '|';
                }

                if ($type === ParameterType::ARRAY) {
                    $hasArray = true;
                }

                if ($type === ParameterType::OBJECT) {
                    $parameters .= $optionalParameter->subObject->name;
                }

                $parameters .= $type->getDefinitionName();
                $multipleTypes = true;
            }

            $parameters .= ' $' . $optionalParameter->formattedName;

            if ($hasArray) {
                $parameters .= ' = [],' . PHP_EOL;
                continue;
            }

            $parameters .= ' = null,' . PHP_EOL;
        }

        return $parameters;
    }

    public function generateDocblock(): string
    {
        $hasDocblock = false;
        $docblock = '**';

        foreach ($this->parameters as $parameter) {
            if (is_array($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= PHP_EOL . '*@var ';

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

        $docblock .= PHP_EOL . '**';
        return $docblock;
    }
}