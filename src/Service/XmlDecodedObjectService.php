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

        $multipleParameters = false;
        $hydrationValidation .= 'if (';

        /**
         * @var XmlObjectParameter $requiredParameter
         */
        foreach ($requiredParameters as $requiredParameter) {
            if ($multipleParameters) {
                $hydrationValidation .= ' || ';
            }

            $hydrationValidation .= $this->generateSingleValidationRule($requiredParameter);

            $multipleParameters = true;
        }

        $hydrationValidation .= ') {' . PHP_EOL
            . "\t\t" . 'throw new \RuntimeException(\'Missing required parameter\');' . PHP_EOL
            . '}' . PHP_EOL;

        return $hydrationValidation;
    }

    private function generateSingleValidationRule(XmlObjectParameter $parameter): string
    {
        if ($parameter->isAttribute) {
            return '!isset($data[\'' . $parameter->originalName . '\'])';
        }

        return '$data->' . $parameter->originalName . '->__toString() === \'\'';
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
        $parameter = '';

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
        $parameter = '';

        if ($optional) {
            $parameter .= '$data->' . $objectParameter->originalName . '->__toString() !== \'\' ? ';
        }

        if ($objectParameter->subObject && $objectParameter->hasType(ParameterType::OBJECT)) {
            $parameter .= $objectParameter->subObject->name . '::hydrate($data->' . $objectParameter->originalName . ')';
        }

        if (isset($objectParameter->arrayTypes[0]) && $objectParameter->arrayTypes[0] instanceof DecodedObject && $objectParameter->hasType(ParameterType::ARRAY)) {
            return $objectParameter->arrayTypes[0]->name . '::hydrateMany(iterator_to_array($data->' . $objectParameter->originalName . ')),' . PHP_EOL;
        }

        if (!$objectParameter->hasType(ParameterType::OBJECT)) {
            $parameterType = $this->getPrimaryType($objectParameter);

            if ($parameterType !== ParameterType::STRING) {
                $parameter .= '(' . $parameterType->getDefinitionName() . ') ';
            }

            $parameter .=  '$data->' . $objectParameter->originalName . '->__toString()';
        }

        if ($optional) {
            $parameter .= ' : null';
        }

        return $parameter . ',' . PHP_EOL;
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
