<?php

namespace LiamH\Valueobjectgenerator\Service;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;

class DecodedObjectService
{
    public function generateParameters(DecodedObject $decodedObject): string
    {
        $parameters = '';

        foreach ($decodedObject->getRequiredParameters() as $requiredParameter) {
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

        foreach ($decodedObject->getOptionalParameters() as $optionalParameter) {
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

    public function generateHydrationLogic(DecodedObject $decodedObject): string
    {
        $hydrationLogic = '';

        foreach ($decodedObject->getRequiredParameters() as $requiredParameter) {
            $hydrationLogic .= $this->generateParameterHydration(
                objectParameter: $requiredParameter,
                optionalParameter: false,
            );
        }

        foreach ($decodedObject->getOptionalParameters() as $optionalParameter) {
            $hydrationLogic .= $this->generateParameterHydration(
                objectParameter: $optionalParameter,
                optionalParameter: true,
            );
        }

        return $hydrationLogic;
    }

    private function generateParameterHydration(ObjectParameter $objectParameter, bool $optionalParameter): string
    {
        $hydrationLogic = '';

        if ($objectParameter->subObject && $objectParameter->hasType(ParameterType::OBJECT)) {
            if ($optionalParameter) {
                return $objectParameter->formattedName . ': isset($data[\'' . $objectParameter->originalName . '\'] ?' . $objectParameter->subObject->name . '::hydrate($data[\'' . $objectParameter->originalName . '\']) : null,' . PHP_EOL;
            }

            return $objectParameter->formattedName . ': ' . $objectParameter->subObject->name . '::hydrate($data[\'' . $objectParameter->originalName . '\']),' . PHP_EOL;
        }

        if ($objectParameter->hasType(ParameterType::ARRAY) && isset($objectParameter->arrayTypes[0]) && $objectParameter->arrayTypes[0] instanceof DecodedObject) {
            $hydrationLogic = $objectParameter->formattedName . ': ' . $objectParameter->arrayTypes[0]->name . '::hydrateMany($data[\'' . $objectParameter->originalName . '\']';

            if ($optionalParameter) {
                $hydrationLogic .= '?? []),' . PHP_EOL;
                return $hydrationLogic;
            }

            $hydrationLogic .= '),' . PHP_EOL;
            return $hydrationLogic;
        }

        $hydrationLogic = $objectParameter->formattedName . ': $data[\'' . $objectParameter->originalName . '\']';

        if ($optionalParameter) {
            $hydrationLogic .= ' ?? ' . ($objectParameter->hasType(ParameterType::ARRAY) ? '[]' : 'null');
        }

        return $hydrationLogic . ',' . PHP_EOL;
    }

    public function generateDocblock(DecodedObject $decodedObject): string
    {
        $hasDocblock = false;
        $docblock = '/**';

        foreach ($decodedObject->parameters as $parameter) {
            if (is_array($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= PHP_EOL . "\t " . '* @var ';
                foreach ($parameter->arrayTypes as $key => $type) {
                    if ($key > 0) {
                        $docblock .= '|';
                    }

                    if ($type instanceof DecodedObject) {
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

        $docblock .= PHP_EOL . "\t " . '*/';
        return $docblock;
    }
}
