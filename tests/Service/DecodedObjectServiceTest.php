<?php

namespace Service;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\ObjectParameter;
use PHPUnit\Framework\TestCase;

class DecodedObjectServiceTest extends TestCase
{
    /**
     * @dataProvider docblockProvider
     */
    public function testGenerateDocblock(DecodedObject $object, string $expectedDocblock): void
    {
        $service = $this->createService();
        $result = $service->generateDocblock($object);

        self::assertSame($expectedDocblock, $result);
    }

    public static function docblockProvider(): array
    {
        $stringParameter = new ObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new ObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new ObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new ObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $objectParameter = new ObjectParameter('booleanType', 'booleanType', [ParameterType::OBJECT], [], new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new ObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'No Docblock' => [new DecodedObject('NoDoc', [$stringParameter, $integerParameter, $floatParameter, $booleanParameter, $objectParameter, $nullParameter]), ''],
            'Required Singular Int Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER])]), "/**" . PHP_EOL . "\t * @var int[] \$intArray" . PHP_EOL . "\t */"],
            'Required Singular Float Array' => [new DecodedObject('FloatArray', [new ObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT])]), "/**" . PHP_EOL . "\t * @var float[] \$floatArray" . PHP_EOL . "\t */"],
            'Required Singular String Array' => [new DecodedObject('StringArray', [new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "\t * @var string[] \$stringArray" . PHP_EOL . "\t */"],
            'Required Singular Object Array' => [new DecodedObject('ObjectArray', [new ObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter])])]), "/**" . PHP_EOL . "\t * @var Object[] \$objectArray" . PHP_EOL . "\t */"],
            'Required Singular Mixed Array' => [new DecodedObject('MixedArray', [new ObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN])]), "/**" . PHP_EOL . "\t * @var Object[]|string[]|int[]|float[]|bool[] \$mixedArray" . PHP_EOL . "\t */"],
            'Optional Singular Int Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER, ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?int[] \$intArray" . PHP_EOL . "\t */"],
            'Optional Singular Float Array' => [new DecodedObject('FloatArray', [new ObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT, ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?float[] \$floatArray" . PHP_EOL . "\t */"],
            'Optional Singular String Array' => [new DecodedObject('StringArray', [new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING, ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?string[] \$stringArray" . PHP_EOL . "\t */"],
            'Optional Singular Object Array' => [new DecodedObject('ObjectArray', [new ObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?Object[] \$objectArray" . PHP_EOL . "\t */"],
            'Optional Singular Mixed Array' => [new DecodedObject('MixedArray', [new ObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN, ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?Object[]|?string[]|?int[]|?float[]|?bool[] \$mixedArray" . PHP_EOL . "\t */"],
            'Multi Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER]), new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "\t * @var int[] \$intArray" . PHP_EOL . "\t * @var string[] \$stringArray" . PHP_EOL . "\t */"],
            'Empty Array' => [new DecodedObject('EmptyArray', [new ObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [])]), ''],
            'Null Array' => [new DecodedObject('EmptyArray', [new ObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [ParameterType::NULL])]), "/**" . PHP_EOL . "\t * @var ?mixed[] \$emptyArray" . PHP_EOL . "\t */"],
        ];
    }

    /**
     * @dataProvider parameterHydrationProvider
     */
    public function testGenerateParameterHydration(DecodedObject $object, string $expectedParameterHydration): void
    {
        $service = $this->createService();
        $result = $service->generateHydrationLogic($object);

        self::assertSame($expectedParameterHydration, $result);
    }

    public static function parameterHydrationProvider(): array
    {
        $stringParameter = new ObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new ObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new ObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new ObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $objectParameter = new ObjectParameter('objectType', 'objectType', [ParameterType::OBJECT], [], new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new ObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'Singular Standard Type' => [new DecodedObject('String', [$stringParameter]), "stringType: \$data['stringType']," . PHP_EOL],
            'Singular Object Type' => [new DecodedObject('Object', [$objectParameter]), "objectType: Object::hydrate(\$data['objectType'])," . PHP_EOL],
            'Singular Array Standard Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY])]), "arrayType: \$data['arrayType']," . PHP_EOL],
            'Singular Array Object Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter])])]), "arrayType: Object::hydrateMany(\$data['arrayType'])," . PHP_EOL],
            'Singular Nullable Standard Type' => [new DecodedObject('String', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]), "stringType: \$data['stringType'] ?? null," . PHP_EOL],
            'Singular Nullable Object Type' => [new DecodedObject('Object', [new ObjectParameter('objectType', 'objectType', [ParameterType::OBJECT, ParameterType::NULL], [], new DecodedObject('Object', [$stringParameter]))]), "objectType: isset(\$data['objectType']) ? Object::hydrate(\$data['objectType']) : null," . PHP_EOL],
            'Singular Nullable Array Standard Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL])]), "arrayType: \$data['arrayType'] ?? []," . PHP_EOL],
            'Singular Nullable Array Object Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL], [new DecodedObject('Object', [$stringParameter])])]), "arrayType: Object::hydrateMany(\$data['arrayType'] ?? [])," . PHP_EOL],
            'Multi Types' => [new DecodedObject('NoDoc', [$stringParameter, $integerParameter, $floatParameter, $booleanParameter, $objectParameter, $nullParameter]), "stringType: \$data['stringType']," . PHP_EOL . "intType: \$data['intType']," . PHP_EOL . "floatType: \$data['floatType']," . PHP_EOL . "booleanType: \$data['booleanType']," . PHP_EOL . "objectType: Object::hydrate(\$data['objectType'])," . PHP_EOL . "nullType: \$data['nullType'] ?? null," . PHP_EOL],
        ];
    }

    private function createService(): DecodedObjectService
    {
        return new DecodedObjectService();
    }
}