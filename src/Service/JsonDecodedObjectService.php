<?php

namespace LiamH\ValueObjectCompiler\Service;

use LiamH\ValueObjectCompiler\Enum\HydrationParameter;
use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;

class JsonDecodedObjectService extends DecodedObjectService
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
        $hydrationValidation .= 'if (!isset(';

        foreach ($requiredParameters as $requiredParameter) {
            if ($multipleParameters) {
                $hydrationValidation .= ',';
            }

            $hydrationValidation .= '$data[\'' . $requiredParameter->originalName . '\']';

            $multipleParameters = true;
        }

        $hydrationValidation .= ')) {' . PHP_EOL
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
        return HydrationParameter::ARRAY;
    }

    protected function generateParameterHydration(ObjectParameter $objectParameter, bool $optionalParameter): string
    {
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
}
