<?php

namespace LiamH\Valueobjectgenerator\Service;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;

class JsonDecodedObjectService implements DecodedObjectService
{
    private const NULLABLE = '?';

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
            $multipleTypes = count($optionalParameter->types) > 2;
            $hasArray = false;

            if (count($optionalParameter->types) === 1 && $optionalParameter->hasType(ParameterType::NULL)) {
                $parameters .= '?mixed';
            }

            foreach ($optionalParameter->types as $key => $type) {
                if ($type === ParameterType::NULL && !$multipleTypes) {
                    continue;
                }

                if ($multipleTypes && $key > 0) {
                    $parameters .= '|';
                }

                if ($type === ParameterType::ARRAY) {
                    $hasArray = true;
                }

                if (!$multipleTypes) {
                    $parameters .= self::NULLABLE;
                }

                if ($type === ParameterType::OBJECT && $optionalParameter->subObject) {
                    $parameters .= $optionalParameter->subObject->name;
                    continue;
                }

                $parameters .= $type->getDefinitionName();
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

    public function generateHydrationValidation(DecodedObject $decodedObject): string
    {
        $hydrationValidation = '';
        $requiredParameters = $decodedObject->getRequiredParameters();

        if (!count($requiredParameters)) {
            return $hydrationValidation;
        }

        $multipleParameters = false;
        $hydrationValidation .= 'if (!isset(';

        foreach ($requiredParameters as $requiredParameter) {
            if ($multipleParameters) {
                $hydrationValidation .= ',';
            }

            $hydrationValidation .= '$data[\'' . $requiredParameter->formattedName . '\']';

            $multipleParameters = true;
        }

        $hydrationValidation .= ')) {' . PHP_EOL
            . "\t\t" . 'throw new \RuntimeException(\'Missing required parameter\');' . PHP_EOL
            . '}' . PHP_EOL;

        return $hydrationValidation;
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
                return $objectParameter->formattedName . ': isset($data[\'' . $objectParameter->originalName . '\']) ? ' . $objectParameter->subObject->name . '::hydrate($data[\'' . $objectParameter->originalName . '\']) : null,' . PHP_EOL;
            }

            return $objectParameter->formattedName . ': ' . $objectParameter->subObject->name . '::hydrate($data[\'' . $objectParameter->originalName . '\']),' . PHP_EOL;
        }

        if ($objectParameter->hasType(ParameterType::ARRAY) && isset($objectParameter->arrayTypes[0]) && $objectParameter->arrayTypes[0] instanceof DecodedObject) {
            $hydrationLogic = $objectParameter->formattedName . ': ' . $objectParameter->arrayTypes[0]->name . '::hydrateMany($data[\'' . $objectParameter->originalName . '\']';

            if ($optionalParameter) {
                $hydrationLogic .= ' ?? []),' . PHP_EOL;
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
            if (count($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= PHP_EOL . "\t " . '* @var ';
                $isNullable = $parameter->hasArrayType(ParameterType::NULL);
                $types = 0;
                $totalTypes = count($parameter->arrayTypes);

                foreach ($parameter->arrayTypes as $key => $type) {
                    if ($type === ParameterType::NULL && $totalTypes > 1) {
                        continue;
                    }

                    if ($types > 0) {
                        $docblock .= '|';
                    }

                    if ($isNullable) {
                        $docblock .= '?';

                        if ($totalTypes === 1) {
                            $docblock .= 'mixed[]';
                            continue;
                        }
                    }

                    $types++;

                    if ($type instanceof DecodedObject) {
                        $docblock .= $type->name . '[]';
                        continue;
                    }

                    $docblock .= $type->getDefinitionName() . '[]';
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
