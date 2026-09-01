<?php

namespace LiamH\ValueObjectCompiler\Service;

use LiamH\ValueObjectCompiler\Enum\HydrationParameter;
use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
use LiamH\ValueObjectCompiler\ValueObject\XmlObjectParameter;

abstract class DecodedObjectService
{
    protected const NULLABLE = '?';

    abstract public function generateParameters(DecodedObject $decodedObject): string;
    abstract public function generateHydrationValidation(DecodedObject $decodedObject): string;
    abstract public function generateDocblock(DecodedObject $decodedObject): string;
    abstract public function getHydrationParameter(): HydrationParameter;

    abstract protected function generateParameterHydration(ObjectParameter|XmlObjectParameter $objectParameter, bool $optionalParameter): string;


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

    public function genericParameterGenerator(DecodedObject $decodedObject): string
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

    public function genericDocblockGenerator(DecodedObject $decodedObject): string
    {
        $hasDocblock = false;
        $docblock = '/**';

        foreach ($decodedObject->parameters as $parameter) {
            if (count($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= PHP_EOL . '     ' . '* @param ';
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

        $docblock .= PHP_EOL . '     ' . '*/';
        return $docblock;
    }
}
