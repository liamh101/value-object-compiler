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

                if ($type === ParameterType::OBJECT && $requiredParameter->subObject) {
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

            if (count($optionalParameter->types) === 1 && $optionalParameter->hasType(ParameterType::NULL)) {
                $parameters .= 'mixed';
            }

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

                if ($type === ParameterType::OBJECT && $optionalParameter->subObject) {
                    $parameters .= ParameterType::NULL->getDefinitionName() . $optionalParameter->subObject->name;
                }

                $parameters .= ParameterType::NULL->getDefinitionName() . $type->getDefinitionName();
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

    public function generateHydrationLogic(): string
    {
        $hydrationLogic = '';

        foreach ($this->getRequiredParameters() as $requiredParameter) {
            if ($requiredParameter->subObject && $requiredParameter->hasType(ParameterType::OBJECT)) {
                $hydrationLogic .= $requiredParameter->formattedName . ': ' . $requiredParameter->subObject->name . '::hydrate($data[\'' . $requiredParameter->originalName . '\']),' . PHP_EOL;
                continue;
            }

            if ($requiredParameter->hasType(ParameterType::ARRAY) && $requiredParameter->arrayTypes[0] instanceof DecodedObject) {
                $hydrationLogic .= $requiredParameter->formattedName . ': ' . $requiredParameter->arrayTypes[0]->name . '::hydrateMany($data[\'' . $requiredParameter->originalName . '\']),' . PHP_EOL;
                continue;
            }

            $hydrationLogic .= $requiredParameter->formattedName . ': $data[\'' . $requiredParameter->originalName . '\'],' . PHP_EOL;
        }

        foreach ($this->getOptionalParameters() as $optionalParameter) {
            if ($optionalParameter->subObject && $optionalParameter->hasType(ParameterType::OBJECT)) {
                $hydrationLogic .= $optionalParameter->formattedName . ': isset($data[\'' . $optionalParameter->originalName . '\'] ?' . $optionalParameter->subObject->name . '::hydrate($data[\'' . $optionalParameter->originalName . '\']) : null,' . PHP_EOL;
                continue;
            }

            if ($optionalParameter->hasType(ParameterType::ARRAY) && $optionalParameter->arrayTypes[0] instanceof DecodedObject) {
                $hydrationLogic .= $optionalParameter->formattedName . ': ' . $optionalParameter->arrayTypes[0]->name . '::hydrateMany($data[\'' . $optionalParameter->originalName . '\'] ?? []),' . PHP_EOL;
                continue;
            }

            $hydrationLogic .= $optionalParameter->formattedName . ': $data[\'' . $optionalParameter->originalName . '\'] ?? ' . ($optionalParameter->hasType(ParameterType::ARRAY) ? '[],' : 'null,') . PHP_EOL;
        }

        return $hydrationLogic;
    }

    public function generateDocblock(): string
    {
        $hasDocblock = false;
        $docblock = '/**';

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

        $docblock .= PHP_EOL . '*/';
        return $docblock;
    }
}
