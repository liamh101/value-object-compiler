<?php

namespace LiamH\Valueobjectgenerator\Reducer;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\Exception\ObjectReducerException;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;

class ObjectReducer
{
    private const MINIMUM_OBJECT_COUNT = 2;

    private DecodedObject $masterObject;

    /**
     * @var array<string, ObjectParameter>
     */
    private array $masterParameters;

    /**
     * @param DecodedObject[] $decodedObjects
     */
    public function __construct(
        private array $decodedObjects,
    ) {
        if (count($this->decodedObjects) < self::MINIMUM_OBJECT_COUNT) {
            throw ObjectReducerException::invalidAmount(self::MINIMUM_OBJECT_COUNT);
        }

        $this->validateObjectArray($this->decodedObjects);

        /** @var DecodedObject $masterObject */
        $masterObject = array_pop($this->decodedObjects);

        $this->masterObject = $masterObject;
        $this->masterParameters = $this->masterObject->parameters;
    }

    public function reduceObjects(): DecodedObject
    {
        foreach ($this->decodedObjects as $decodedObject) {
            $optionalParameterValidationList = array_keys($this->masterParameters);

            foreach ($decodedObject->parameters as $parameterName => $parameter) {
                $foundParam = array_search($parameterName, $optionalParameterValidationList, true);

                if ($foundParam !== false) {
                    array_splice($optionalParameterValidationList, $foundParam, 1);
                }

                if (!isset($this->masterParameters[$parameterName])) {
                    $this->addMissingMasterParameter($parameter);
                    continue;
                }

                if (
                    count($parameter->types) === 1 &&
                    $parameter->types[0] === ParameterType::OBJECT &&
                    $parameter->subObject instanceof DecodedObject &&
                    $this->masterParameters[$parameterName]->subObject instanceof DecodedObject
                ) {
                    $this->masterParameters[$parameterName] = new ObjectParameter(
                        originalName: $parameter->originalName,
                        formattedName: $parameter->formattedName,
                        types: $parameter->types,
                        subObject: (new ObjectReducer([$parameter->subObject, $this->masterParameters[$parameterName]->subObject]))->reduceObjects(),
                        arrayTypes: $parameter->arrayTypes,
                    );
                    continue;
                }

                $this->updateExistingParameter($parameter);
            }

            if (count($optionalParameterValidationList) === 0) {
                continue;
            }

            foreach ($optionalParameterValidationList as $optionalParameterName) {
                $this->setParameterAsNullable($optionalParameterName);
            }
        }

        $this->reduceChildArrayTypes();

        return new DecodedObject(
            $this->masterObject->name,
            $this->masterParameters,
        );
    }

    private function updateExistingParameter(ObjectParameter $parameter): void
    {
        $newTypes = $parameter->types;
        $newArrayTypes = $parameter->arrayTypes;

        foreach ($this->masterParameters[$parameter->formattedName]->types as $type) {
            if (!in_array($type, $newTypes, true)) {
                $newTypes[] = $type;
            }
        }

        foreach ($this->masterParameters[$parameter->formattedName]->arrayTypes as $arrayType) {
            if (!in_array($arrayType, $newArrayTypes, true)) {
                $newArrayTypes[] = $arrayType;
            }
        }

        $this->masterParameters[$parameter->formattedName] = new ObjectParameter(
            originalName: $parameter->originalName,
            formattedName: $parameter->formattedName,
            types: $newTypes,
            arrayTypes: $newArrayTypes,
            subObject: $parameter->subObject,
        );
    }

    private function addMissingMasterParameter(ObjectParameter $parameter): void
    {
        $newTypes = $parameter->types;

        if (!in_array(ParameterType::NULL, $newTypes)) {
            $newTypes[] = ParameterType::NULL;
        }

        $this->masterParameters[$parameter->formattedName] = new ObjectParameter(
            originalName: $parameter->originalName,
            formattedName: $parameter->formattedName,
            types: $newTypes,
            arrayTypes: $parameter->arrayTypes,
            subObject: $parameter->subObject,
        );
    }

    private function setParameterAsNullable(string $parameterName): void
    {
        $duplicateObject = $this->masterParameters[$parameterName];
        $newTypes = $this->masterParameters[$parameterName]->types;

        if (!in_array(ParameterType::NULL, $newTypes)) {
            $newTypes[] = ParameterType::NULL;
        }

        $this->masterParameters[$parameterName] = new ObjectParameter(
            originalName: $duplicateObject->originalName,
            formattedName: $duplicateObject->formattedName,
            types: $newTypes,
            arrayTypes: $duplicateObject->arrayTypes,
            subObject: $duplicateObject->subObject,
        );
    }

    private function reduceChildArrayTypes(): void
    {
        foreach ($this->masterParameters as $key => $masterParameter) {
            if (!$masterParameter->hasType(ParameterType::ARRAY)) {
                continue;
            }

            $decodedObjects = array_filter($masterParameter->arrayTypes, static fn ($type) => $type instanceof DecodedObject);
            $additionalTypes = array_filter($masterParameter->arrayTypes, static fn ($type) => !$type instanceof DecodedObject);

            if (count($decodedObjects) < self::MINIMUM_OBJECT_COUNT) {
                continue;
            }

            $additionalTypes[] = (new ObjectReducer($decodedObjects))->reduceObjects();

            $this->masterParameters[$key] = new ObjectParameter(
                originalName: $masterParameter->originalName,
                formattedName: $masterParameter->formattedName,
                types: $masterParameter->types,
                arrayTypes: $additionalTypes,
                subObject: $masterParameter->subObject,
            );
        }
    }

    /**
     * @param mixed[] $decodedObjects
     */
    private function validateObjectArray(array $decodedObjects): void
    {
        foreach ($decodedObjects as $decodedObject) {
            if (!$decodedObject instanceof DecodedObject) {
                throw ObjectReducerException::invalidReduceType($decodedObject);
            }
        }
    }
}
