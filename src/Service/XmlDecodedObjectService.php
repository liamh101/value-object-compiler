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
        $parameters = '';

        foreach ($decodedObject->getRequiredParameters() as $requiredParameter) {
            $parameters .= 'public ';

            if ($requiredParameter->hasType(ParameterType::ARRAY)) {
                $parameters .= 'array $' . $requiredParameter->formattedName . ',' . PHP_EOL;
                continue;
            }

            if ($requiredParameter->subObject && $requiredParameter->hasType(ParameterType::OBJECT)) {
                $parameters .= $requiredParameter->subObject->name . ' $' . $requiredParameter->formattedName . ',' . PHP_EOL;
                continue;
            }

            $type = $this->getPrimaryType($requiredParameter->types);

            $parameters .= $type->getDefinitionName() . ' $' . $requiredParameter->formattedName . ',' . PHP_EOL;
        }

        foreach ($decodedObject->getOptionalParameters() as $optionalParameter) {
            $parameters .= 'public ?';

            if ($optionalParameter->hasType(ParameterType::ARRAY)) {
                $parameters .= 'array $' . $optionalParameter->formattedName . ' = [],' . PHP_EOL;
                continue;
            }

            if ($optionalParameter->subObject && $optionalParameter->hasType(ParameterType::OBJECT)) {
                $parameters .= $optionalParameter->subObject->name . ' $' . $optionalParameter->formattedName . ' = null,' . PHP_EOL;
                continue;
            }

            $type = $this->getPrimaryType($optionalParameter->types);
            $parameters .= $type->getDefinitionName() . ' $' . $optionalParameter->formattedName . ' = null,' . PHP_EOL;
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

            $objectValidation .= '(string)' . $this->getValueParsedName($requiredParameter) . ' === \'\'';
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
        $hasDocblock = false;
        $docblock = '/**';

        foreach ($decodedObject->parameters as $parameter) {
            if (count($parameter->arrayTypes) && $parameter->hasType(ParameterType::ARRAY)) {
                $hasDocblock = true;
                $docblock .= PHP_EOL . '     ' . '* @param ';
                $isNullable = $parameter->hasArrayType(ParameterType::NULL);
                $hasObject = isset($parameter->arrayTypes[0]) && $parameter->arrayTypes[0] instanceof DecodedObject;

                if ($isNullable) {
                    $docblock .= '?';
                }

                if ($hasObject) {
                    $docblock .= $parameter->arrayTypes[0]->name . '[] $' . $parameter->formattedName;
                    continue;
                }

                $type = $this->getPrimaryType($parameter->arrayTypes);
                $docblock .= $type->getDefinitionName() . '[] $' . $parameter->formattedName;
            }
        }

        if (!$hasDocblock) {
            return '';
        }

        $docblock .= PHP_EOL . '     ' . '*/';
        return $docblock;
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

        $parameterType = $this->getPrimaryType($objectParameter->types);

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
            $parameter .= '(string)' . $this->getValueParsedName($objectParameter) . ' !== \'\' ? ';
        }

        if ($objectParameter->subObject && $objectParameter->hasType(ParameterType::OBJECT)) {
            $parameter .= $objectParameter->subObject->name . '::hydrate(' . $this->getValueParsedName($objectParameter) . ')';
        }

        if (isset($objectParameter->arrayTypes[0]) && $objectParameter->arrayTypes[0] instanceof DecodedObject && $objectParameter->hasType(ParameterType::ARRAY)) {
            return $objectParameter->formattedName . ': ' . $objectParameter->arrayTypes[0]->name . '::hydrateMany(iterator_to_array(' . $this->getValueParsedName($objectParameter) . ', false)),' . PHP_EOL;
        }

        if ($objectParameter->hasType(ParameterType::ARRAY)) {
            $parameterType = $this->getPrimaryType($objectParameter->arrayTypes);
            $parser = match ($parameterType) {
                ParameterType::INTEGER => 'intval',
                ParameterType::FLOAT => 'floatval',
                default => 'strval',
            };

            return $objectParameter->formattedName . ': ' . 'array_map(\'' . $parser . '\', iterator_to_array(' . $this->getValueParsedName($objectParameter) . ', false)),' . PHP_EOL;
        }

        if (!$objectParameter->hasType(ParameterType::OBJECT)) {
            $parameterType = $this->getPrimaryType($objectParameter->types);

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

    /**
     * @param ParameterType[]|DecodedObject[] $types
     */
    private function getPrimaryType(array $types): ParameterType
    {
        $finalType = null;

        foreach ($types as $type) {
            if (
                $type === ParameterType::NULL
                || $type instanceof DecodedObject
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
