<?php

namespace LiamH\Valueobjectgenerator\Generator;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;

class JsonGenerator
{
    public function generateClassFromSource(string $parentName, string $rawJson): DecodedObject
    {
        $formattedJson = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($formattedJson)) {
            throw new \RuntimeException('Invalid JSON provided');
        }

        return $this->generateObject($parentName, $formattedJson);
    }

    private function generateObject(string $objectName, array $formattedJson): DecodedObject
    {
        /** @var ObjectParameter[] $parameters */
        $parameters = [];

        foreach ($formattedJson as $name => $value) {
            $parameter = ParameterType::from(gettype($value));
            $parameterName = $this->createVariableName($name);

            if (isset($parameters[$parameterName])) {
                $parameters[$parameterName] = $this->updateExistingParameter($parameters[$parameterName], $parameter);
                continue;
            }

            if ($parameter === ParameterType::ARRAY) {
                $parameters[$parameterName] = $this->handleArrayType($value, $name, $parameterName);
                continue;
            }

            $parameters[$parameterName] = new ObjectParameter(
                originalName: $name,
                formattedName: $parameterName,
                types: [$parameter]
            );
        }

        return new DecodedObject($this->createClassName($objectName), $parameters);
    }

    private function handleArrayType(array $arrayValue, string $originalName, string $formattedName): ObjectParameter
    {
        if ($this->isSubclass($arrayValue)) {
            $objectValue = $this->generateObject($formattedName, $arrayValue);
            return new ObjectParameter(
                originalName: $originalName,
                formattedName: $formattedName,
                types: [ParameterType::OBJECT],
                subObject: $objectValue,
            );
        }

        $types = [];
        $objects = [];

        foreach ($arrayValue as $value) {
            $parameter = ParameterType::from(gettype($value));

            if (!in_array($parameter, $types, true)) {
                $types[] = $parameter;
            }

            if ($parameter === ParameterType::ARRAY) {
                $object = $this->handleArrayType(
                    $value,
                    substr($originalName, 0, -1),
                    substr($formattedName, 0, -1)
                );

                foreach ($object->types as $type) {
                    if (!in_array($type, $types, true)) {
                        $types[] = $type;
                    }
                }

                if ($object->types[0] === ParameterType::OBJECT) {
                    $objects[] = $object->subObject;
                }
            }
        }

        if (!count($objects)) {
            return new ObjectParameter(
                originalName: $originalName,
                formattedName: $formattedName,
                types: [ParameterType::ARRAY],
                arrayTypes: $types,
            );
        }

        if (count($objects) === 1) {
            return new ObjectParameter(
                originalName: $originalName,
                formattedName: $formattedName,
                types: [ParameterType::ARRAY],
                arrayTypes: $objects,
            );
        }

        $refinedObject = $this->reduceObjects($objects);

        return new ObjectParameter(
            originalName: $originalName,
            formattedName: $formattedName,
            types: [ParameterType::ARRAY],
            arrayTypes: [$refinedObject],
        );
    }

    /**
     * @param DecodedObject[] $decodedObjects
     * @return DecodedObject
     */
    private function reduceObjects(array $decodedObjects): DecodedObject
    {
        $masterObject = $decodedObjects[0];
        $masterParameters = $masterObject->parameters;

        foreach ($decodedObjects as $decodedObject) {
            $duplicateKeys = array_keys($masterParameters);

            foreach ($decodedObject->parameters as $parameterName => $parameter) {
                $foundPara = array_search($parameterName, $decodedObjects, true);

                if (!$foundPara !== false) {
                    unset($duplicateKeys[$foundPara]);
                }

                if (!isset($masterParameters[$parameterName])) {
                    $newTypes = $parameter->types;

                    if (!in_array(ParameterType::NULL, $newTypes)) {
                        $newTypes[] = ParameterType::NULL;
                    }

                    $masterParameters[$parameterName] = new ObjectParameter(
                        originalName: $parameter->originalName,
                        formattedName: $parameter->formattedName,
                        types: $newTypes,
                        subObject: $parameter->subObject,
                        arrayTypes: $parameter->arrayTypes,
                    );
                    continue;
                }

                if (count($parameter->types) === 1 && $parameter->types[0] === ParameterType::OBJECT) {
                    $subObject = $this->reduceObjects([$parameter->subObject, $masterParameters[$parameterName]->subObject]);
                    $masterParameters[$parameterName] = new ObjectParameter(
                        originalName: $parameter->originalName,
                        formattedName: $parameter->formattedName,
                        types: $parameter->types,
                        subObject: $subObject,
                        arrayTypes: $parameter->arrayTypes,
                    );
                    continue;
                }

                $newTypes = $parameter->types;
                $newArrayTypes = $parameter->arrayTypes;

                foreach ($masterParameters[$parameterName]->types as $type) {
                    if (!in_array($type, $newTypes, true)) {
                        $newTypes[] = $type;
                    }
                }

                foreach ($masterParameters[$parameterName]->arrayTypes as $arrayType) {
                    if (!in_array($arrayType, $newArrayTypes, true)) {
                        $newArrayTypes[] = $arrayType;
                    }
                }

                $masterParameters[$parameterName] = new ObjectParameter(
                    originalName: $parameter->originalName,
                    formattedName: $parameter->formattedName,
                    types: $newTypes,
                    subObject: $parameter->subObject,
                    arrayTypes: $newArrayTypes,
                );
            }

            if (count($duplicateKeys) === 0) {
                continue;
            }

            foreach ($duplicateKeys as $duplicateKey) {
                $duplicateObject = $masterParameters[$duplicateKey];
                $newTypes = $masterParameters[$duplicateKey]->types;

                if (!in_array(ParameterType::NULL, $newTypes)) {
                    $newTypes[] = ParameterType::NULL;
                }

                $masterParameters[$duplicateKey] = new ObjectParameter(
                    originalName: $duplicateObject->originalName,
                    formattedName: $duplicateObject->formattedName,
                    types: $newTypes,
                    subObject: $duplicateObject->subObject,
                    arrayTypes: $duplicateObject->arrayTypes,
                );
            }
        }

        return new DecodedObject(
            $masterObject->name,
            $masterParameters,
        );
    }

    private function updateExistingParameter(ObjectParameter $parameter, ParameterType $newType): ObjectParameter
    {
        if ($parameter->hasType($newType)) {
            return $parameter;
        }

        $types = $parameter->types;
        $types[] = $newType;

        return new ObjectParameter(
            originalName: $parameter->originalName,
            formattedName: $parameter->formattedName,
            types: $types,
            subObject: $parameter->subObject,
        );
    }


    private function isSubclass(array $potentialSubclass): bool
    {
        $keys = array_keys($potentialSubclass);

        foreach ($keys as $key) {
            $validKey = is_string($key);

            if ($validKey) {
                return true;
            }
        }

        return false;
    }

    private function createClassName(string $name): string
    {
        return $this->createName($name);
    }

    private function createVariableName(string $name): string
    {
        return lcfirst($this->createName($name));
    }

    private function createName(string $name): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $name));

        return implode('', array_map(static fn ($word) => ucfirst($word), $words));
    }
}
