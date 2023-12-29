<?php

namespace Service;

use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Service\JsonDecodedObjectService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\ObjectParameter;
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
            'Required Singular Int Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER])]), "/**" . PHP_EOL . "     * @param int[] \$intArray" . PHP_EOL . "     */"],
            'Required Singular Float Array' => [new DecodedObject('FloatArray', [new ObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT])]), "/**" . PHP_EOL . "     * @param float[] \$floatArray" . PHP_EOL . "     */"],
            'Required Singular String Array' => [new DecodedObject('StringArray', [new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "     * @param string[] \$stringArray" . PHP_EOL . "     */"],
            'Required Singular Object Array' => [new DecodedObject('ObjectArray', [new ObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter])])]), "/**" . PHP_EOL . "     * @param Object[] \$objectArray" . PHP_EOL . "     */"],
            'Required Singular Mixed Array' => [new DecodedObject('MixedArray', [new ObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN])]), "/**" . PHP_EOL . "     * @param Object[]|string[]|int[]|float[]|bool[] \$mixedArray" . PHP_EOL . "     */"],
            'Optional Singular Int Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?int[] \$intArray" . PHP_EOL . "     */"],
            'Optional Singular Float Array' => [new DecodedObject('FloatArray', [new ObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?float[] \$floatArray" . PHP_EOL . "     */"],
            'Optional Singular String Array' => [new DecodedObject('StringArray', [new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?string[] \$stringArray" . PHP_EOL . "     */"],
            'Optional Singular Object Array' => [new DecodedObject('ObjectArray', [new ObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?Object[] \$objectArray" . PHP_EOL . "     */"],
            'Optional Singular Mixed Array' => [new DecodedObject('MixedArray', [new ObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?Object[]|?string[]|?int[]|?float[]|?bool[] \$mixedArray" . PHP_EOL . "     */"],
            'Multi Array' => [new DecodedObject('IntArray', [new ObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER]), new ObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "     * @param int[] \$intArray" . PHP_EOL . "     * @param string[] \$stringArray" . PHP_EOL . "     */"],
            'Empty Array' => [new DecodedObject('EmptyArray', [new ObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [])]), ''],
            'Null Array' => [new DecodedObject('EmptyArray', [new ObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?mixed[] \$emptyArray" . PHP_EOL . "     */"],
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

    /**
     * @dataProvider parameterValidationProvider
     */
    public function testGenerateParameterValidation(DecodedObject $object, string $expectedValidation): void
    {
        $service = $this->createService();
        $result = $service->generateHydrationValidation($object);

        self::assertSame($expectedValidation, $result);
    }

    public static function parameterValidationProvider(): array
    {
        return [
            'Single Required' => [
                new DecodedObject('String', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING])]),
                "if (!isset(\$data['stringType'])) {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Multiple Required' => [
                new DecodedObject('String', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING]), new ObjectParameter('intType', 'intType', [ParameterType::INTEGER])]),
                "if (!isset(\$data['stringType'],\$data['intType'])) {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Mixed Parameters' => [
                new DecodedObject('String', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING]), new ObjectParameter('intType', 'intType', [ParameterType::INTEGER, ParameterType::NULL])]),
                "if (!isset(\$data['stringType'])) {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Single Nullable' => [
                new DecodedObject('String', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]),
                '',
            ],
        ];
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGenerateParameter(DecodedObject $object, string $expectedParameter): void
    {
        $service = $this->createService();
        $result = $service->generateParameters($object);

        self::assertSame($expectedParameter, $result);
    }

    public static function parameterProvider(): array
    {
        $stringParameter = new ObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new ObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new ObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new ObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $objectParameter = new ObjectParameter('objectType', 'objectType', [ParameterType::OBJECT], [], new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new ObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'Singular Required String Type' => [new DecodedObject('String', [$stringParameter]), "public string \$stringType," . PHP_EOL],
            'Singular Required Int Type' => [new DecodedObject('Int', [$integerParameter]), "public int \$intType," . PHP_EOL],
            'Singular Required Float Type' => [new DecodedObject('Float', [$floatParameter]), "public float \$floatType," . PHP_EOL],
            'Singular Required Boolean Type' => [new DecodedObject('Bool', [$booleanParameter]), "public bool \$booleanType," . PHP_EOL],
            'Singular Required Object Type' => [new DecodedObject('Object', [$objectParameter]), "public Object \$objectType," . PHP_EOL],
            'Singular Required Array Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::STRING])]), "public array \$arrayType," . PHP_EOL],
            'Singular Required Multi Standard Type' => [new DecodedObject('Multi', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::INTEGER])]), "public string|int \$stringType," . PHP_EOL],
            'Singular Required Multi Object Type' => [new DecodedObject('Multi', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::OBJECT], [], new DecodedObject('Object', [$stringParameter]))]), "public string|Object \$stringType," . PHP_EOL],
            'Singular Nullable String Type' => [new DecodedObject('Multi', [new ObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]), "public ?string \$stringType = null," . PHP_EOL],
            'Singular Nullable Int Type' => [new DecodedObject('Multi', [new ObjectParameter('intType', 'intType', [ParameterType::INTEGER, ParameterType::NULL])]), "public ?int \$intType = null," . PHP_EOL],
            'Singular Nullable Float Type' => [new DecodedObject('Multi', [new ObjectParameter('floatType', 'floatType', [ParameterType::FLOAT, ParameterType::NULL])]), "public ?float \$floatType = null," . PHP_EOL],
            'Singular Nullable Bool Type' => [new DecodedObject('Multi', [new ObjectParameter('boolType', 'boolType', [ParameterType::BOOLEAN, ParameterType::NULL])]), "public ?bool \$boolType = null," . PHP_EOL],
            'Singular Nullable Type' => [new DecodedObject('Multi', [new ObjectParameter('nullType', 'nullType', [ParameterType::NULL])]), "public ?mixed \$nullType = null," . PHP_EOL],
            'Singular Nullable Object Type' => [new DecodedObject('Multi', [new ObjectParameter('objType', 'objType', [ParameterType::OBJECT, ParameterType::NULL], [], new DecodedObject('Object', [$stringParameter]))]), "public ?Object \$objType = null," . PHP_EOL],
            'Singular Nullable Array Type' => [new DecodedObject('Array', [new ObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL], [ParameterType::STRING])]), "public ?array \$arrayType = []," . PHP_EOL],
            'Singular Nullable Multi Standard Type' => [new DecodedObject('Multi', [new ObjectParameter('intStringType', 'intStringType', [ParameterType::STRING, ParameterType::INTEGER, ParameterType::NULL])]), "public string|int|null \$intStringType = null," . PHP_EOL],
            'Multi Row Required String and Int' => [new DecodedObject('String', [$stringParameter, $integerParameter]), "public string \$stringType," . PHP_EOL . "public int \$intType," . PHP_EOL],
        ];
    }

    private function createService(): JsonDecodedObjectService
    {
        return new JsonDecodedObjectService();
    }
}