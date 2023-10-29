<?php

namespace LiamH\Valueobjectgenerator\Generator;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\Reducer\ObjectReducer;
use LiamH\Valueobjectgenerator\Service\NameService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;

class JsonGenerator implements SourceGenerator
{
    public function __construct(
        private readonly NameService $nameService,
    ) {
    }

    public function generateClassFromSource(string $parentName, string $source): DecodedObject
    {
        $formattedJson = json_decode($source, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($formattedJson)) {
            throw new \RuntimeException('Invalid JSON provided');
        }

        return $this->generateObject($parentName, $formattedJson);
    }

    /**
     * @param string $objectName
     * @param array<string, mixed> $formattedJson
     * @return DecodedObject
     */
    private function generateObject(string $objectName, array $formattedJson): DecodedObject
    {
        /** @var ObjectParameter[] $parameters */
        $parameters = [];

        foreach ($formattedJson as $name => $value) {
            $parameter = ParameterType::from(gettype($value));
            $parameterName = $this->nameService->createVariableName($name);

            if (isset($parameters[$parameterName])) {
                $parameters[$parameterName] = $this->updateExistingParameter($parameters[$parameterName], $parameter);
                continue;
            }

            if ($parameter === ParameterType::ARRAY && is_array($value)) {
                $parameters[$parameterName] = $this->handleArrayType($value, $name, $parameterName);
                continue;
            }

            $parameters[$parameterName] = new ObjectParameter(
                originalName: $name,
                formattedName: $parameterName,
                types: [$parameter]
            );
        }

        return new DecodedObject($this->nameService->createClassName($objectName), $parameters);
    }

    /**
     * @param array<string, mixed> $arrayValue
     * @param string $originalName
     * @param string $formattedName
     * @return ObjectParameter
     * @throws \Exception
     */
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

            if ($parameter === ParameterType::ARRAY && is_array($value)) {
                $object = $this->handleArrayType(
                    $value,
                    $this->nameService->makeSingular($originalName),
                    $this->nameService->makeSingular($formattedName),
                );

                foreach ($object->types as $type) {
                    if (!in_array($type, $types, true)) {
                        $types[] = $type;
                    }
                }

                if ($object->types[0] === ParameterType::OBJECT && $object->subObject instanceof DecodedObject) {
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

        return new ObjectParameter(
            originalName: $originalName,
            formattedName: $formattedName,
            types: [ParameterType::ARRAY],
            arrayTypes: [(new ObjectReducer($objects))->reduceObjects()],
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

    /**
     * @param array<string|int, mixed> $potentialSubclass
     * @return bool
     */
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
}
