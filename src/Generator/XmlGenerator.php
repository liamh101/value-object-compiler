<?php

namespace LiamH\ValueObjectCompiler\Generator;

use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Reducer\ObjectReducer;
use LiamH\ValueObjectCompiler\Service\NameService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
use SimpleXMLElement;

readonly class XmlGenerator implements SourceGenerator
{
    public function __construct(
        private NameService $nameService,
    ) {
    }

    public function generateClassFromSource(string $parentName, string $source): DecodedObject
    {
        $xml = simplexml_load_string($source);

        if ($xml === false) {
            throw new \RuntimeException('Invalid XML provided');
        }

        return $this->generateObject($xml);
    }

    private function generateObject(SimpleXMLElement $element): DecodedObject
    {
        $parameters = [];

        foreach ($element->attributes() as $name => $value) {
            $parameterName = $this->nameService->createVariableName($name);

            $parameters[$parameterName] = new ObjectParameter(
                originalName: $name,
                formattedName: $parameterName,
                types: [$this->determineXmlType($value)],
            );
        }

        foreach ($element->children() as $child) {
            $parameterName = $this->nameService->createVariableName($child->getName());
            $exists = isset($parameters[$parameterName]);
            $parameter = $this->buildObjectParameter($child);

            if ($exists) {
                $originalParameter = $parameters[$parameterName];

                $parameter = $this->handleArrayType($originalParameter, $parameter);
            }

            $parameters[$parameterName] = $parameter;
        }

        return new DecodedObject(
            name: $this->nameService->createVariableName($element->getName()),
            parameters: $parameters,
        );
    }

    private function determineXmlType(string $originalValue): ParameterType
    {
        if ($originalValue === '') {
            return ParameterType::NULL;
        }

        if (is_numeric($originalValue)) {
            if ((float)$originalValue > PHP_INT_MAX || str_contains($originalValue, '.')) {
                return ParameterType::FLOAT;
            }

            return ParameterType::INTEGER;
        }

        if ($originalValue === 'true' || $originalValue === 'false') {
            return ParameterType::BOOLEAN;
        }

        return ParameterType::STRING;
    }

    private function buildObjectParameter(SimpleXMLElement $element): ObjectParameter
    {
        $childName = $element->getName();
        $formattedName = $this->nameService->createVariableName($childName);

        if (count($element->children()) || count($element->attributes())) {
            return new ObjectParameter(
                originalName: $childName,
                formattedName: $formattedName,
                types: [ParameterType::OBJECT],
                subObject: $this->generateObject($element)
            );
        }

        if (trim((string)$element) !== "") {
            return new ObjectParameter(
                originalName: $childName,
                formattedName: $formattedName,
                types: [$this->determineXmlType((string)$element)],
            );
        }


        return new ObjectParameter(
            originalName: $childName,
            formattedName: $formattedName,
            types: [ParameterType::OBJECT],
            subObject: $this->generateObject($element)
        );
    }

    private function handleArrayType(ObjectParameter $predefined, ObjectParameter $element): ObjectParameter
    {
        $types = [];

        if ($element->hasObject()) {
            $types = [...$element->getObjects(), ...$predefined->getObjects()];

            if (count($types) > 1) {
                $types = [(new ObjectReducer($types))->reduceObjects()];
            }
        }

        if ($predefined->hasType(ParameterType::ARRAY)) {
            foreach ($predefined->arrayTypes as $type) {
                if ($type instanceof DecodedObject || $type === ParameterType::OBJECT) {
                    continue;
                }

                $types[] = $type;
            }
        }

        return new ObjectParameter(
            originalName: $predefined->originalName,
            formattedName: $predefined->formattedName,
            types: [ParameterType::ARRAY],
            arrayTypes: $types,
        );
    }
}
