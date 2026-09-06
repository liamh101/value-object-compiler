<?php

namespace LiamH\ValueObjectCompiler\Service;

use LiamH\ValueObjectCompiler\Enum\HydrationParameter;
use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
use LiamH\ValueObjectCompiler\ValueObject\XmlObjectParameter;

class XmlDecodedObjectService extends DecodedObjectService
{
    public function generateParameters(DecodedObject $decodedObject): string
    {
        return $this->genericParameterGenerator($decodedObject);
    }

    public function generateHydrationValidation(DecodedObject $decodedObject): string
    {
        $hydrationValidation = '';
        $requiredParameters = $decodedObject->getRequiredParameters();

        if (!count($requiredParameters)) {
            return $hydrationValidation;
        }

        $attributes = [];
        $multipleParameters = false;
        $hydrationValidation .= 'if (';
        $objectValidation = '';

        /**
         * @var XmlObjectParameter $requiredParameter
         */
        foreach ($requiredParameters as $requiredParameter) {
            if ($requiredParameter->isAttribute) {
                $attributes[] = $requiredParameter->originalName;
                continue;
            }

            if ($multipleParameters) {
                $objectValidation .= ' || ' . PHP_EOL;
            }

            $objectValidation .= $this->getValueParsedName($requiredParameter) . '->__toString() === \'\'';
            $multipleParameters = true;
        }

        if (count($attributes)) {
            $hydrationValidation .= '!isset($data[\'' . implode('\'], $data[\'', $attributes) . '\'])';

            if ($objectValidation !== '') {
                $hydrationValidation .= ' || ' . PHP_EOL;
            }
        }

        $hydrationValidation .= $objectValidation . ') {' . PHP_EOL
            . "\t\t" . 'throw new \RuntimeException(\'Missing required parameter\');' . PHP_EOL
            . '}' . PHP_EOL;

        return $hydrationValidation;
    }

    public function generateDocblock(DecodedObject $decodedObject): string
    {
        return $this->genericDocblockGenerator($decodedObject);
    }

    public function getHydrationParameter(): HydrationParameter
    {
        return HydrationParameter::XML;
    }

    protected function generateParameterHydration(XmlObjectParameter|ObjectParameter $objectParameter, bool $optionalParameter): string
    {
        if (!$objectParameter instanceof XmlObjectParameter) {
            throw new \RuntimeException('Unsupported parameter type');
        }

        if ($objectParameter->isAttribute) {
            return $this->generateAttributeParameter($objectParameter, $optionalParameter);
        }

        return $this->generateValueParameter($objectParameter, $optionalParameter);
    }

    private function generateAttributeParameter(XmlObjectParameter $objectParameter, bool $optional): string
    {
        $parameter = $objectParameter->formattedName . ': ';

        if ($optional) {
            $parameter .= 'isset($data[\'' . $objectParameter->originalName . '\']) ? ';
        }

        $parameterType = $this->getPrimaryType($objectParameter);

        if ($parameterType !== ParameterType::STRING) {
            $parameter .= '(' . $parameterType->getDefinitionName() . ') ';
        }

        $parameter .= '$data[\'' . $objectParameter->originalName . '\']';

        if ($optional) {
            $parameter .= ' : null';
        }

        return $parameter . ',' . PHP_EOL;
    }

    private function generateValueParameter(XmlObjectParameter $objectParameter, bool $optional): string
    {
        $parameter = $objectParameter->formattedName . ': ';

        if ($optional) {
            $parameter .= $this->getValueParsedName($objectParameter) . '->__toString() !== \'\' ? ';
        }

        if ($objectParameter->subObject && $objectParameter->hasType(ParameterType::OBJECT)) {
            $parameter .= $objectParameter->subObject->name . '::hydrate(' . $this->getValueParsedName($objectParameter) . ')';
        }

        if (isset($objectParameter->arrayTypes[0]) && $objectParameter->arrayTypes[0] instanceof DecodedObject && $objectParameter->hasType(ParameterType::ARRAY)) {
            return $objectParameter->formattedName . ': ' . $objectParameter->arrayTypes[0]->name . '::hydrateMany(iterator_to_array(' . $this->getValueParsedName($objectParameter) . ')),' . PHP_EOL;
        }

        if (!$objectParameter->hasType(ParameterType::OBJECT)) {
            $parameterType = $this->getPrimaryType($objectParameter);

            if ($parameterType !== ParameterType::STRING) {
                $parameter .= '(' . $parameterType->getDefinitionName() . ') ';
            }

            $parameter .=  $this->getValueParsedName($objectParameter) . '->__toString()';
        }

        if ($optional) {
            $parameter .= ' : null';
        }

        return $parameter . ',' . PHP_EOL;
    }

    private function getValueParsedName(XmlObjectParameter $parameter): string
    {
        if (preg_match('/[.|\-|:]+/', $parameter->originalName)) {
            return '$data->{\'' . $parameter->originalName . '\'}';
        }

        return '$data->' . $parameter->originalName;
    }

    private function getPrimaryType(XmlObjectParameter $parameter): ParameterType
    {
        $finalType = null;

        foreach ($parameter->types as $type) {
            if (
                $type === ParameterType::NULL
                || $finalType === ParameterType::STRING
                || $finalType === ParameterType::OBJECT
                || $finalType === ParameterType::ARRAY
            ) {
                continue;
            }

            if ($finalType === ParameterType::FLOAT && $type === ParameterType::INTEGER) {
                continue;
            }

            $finalType = $type;
        }

        if (!$finalType) {
            $finalType = ParameterType::STRING;
        }

        return $finalType;
    }
}
