<?php

namespace Service;

use LiamH\ValueObjectCompiler\Enum\ParameterType;
use LiamH\ValueObjectCompiler\Service\JsonDecodedObjectService;
use LiamH\ValueObjectCompiler\Service\XmlDecodedObjectService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\XmlObjectParameter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class XmlDecodedObjectServiceTest extends TestCase
{
    #[DataProvider('docblockProvider')]
    public function testGenerateDocblock(DecodedObject $object, string $expectedDocblock): void
    {
        $service = $this->createService();
        $result = $service->generateDocblock($object);

        self::assertSame($expectedDocblock, $result);
    }

    public static function docblockProvider(): array
    {
        $stringParameter = new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new XmlObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new XmlObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $objectParameter = new XmlObjectParameter('booleanType', 'booleanType', [ParameterType::OBJECT], [], false, new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new XmlObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'No Docblock' => [new DecodedObject('NoDoc', [$stringParameter, $integerParameter, $floatParameter, $booleanParameter, $objectParameter, $nullParameter]), ''],
            'Required Singular Int Array' => [new DecodedObject('IntArray', [new XmlObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER])]), "/**" . PHP_EOL . "     * @param int[] \$intArray" . PHP_EOL . "     */"],
            'Required Singular Float Array' => [new DecodedObject('FloatArray', [new XmlObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT])]), "/**" . PHP_EOL . "     * @param float[] \$floatArray" . PHP_EOL . "     */"],
            'Required Singular Number Array' => [new DecodedObject('FloatArray', [new XmlObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT, ParameterType::INTEGER])]), "/**" . PHP_EOL . "     * @param float[] \$floatArray" . PHP_EOL . "     */"],
            'Required Singular String Array' => [new DecodedObject('StringArray', [new XmlObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "     * @param string[] \$stringArray" . PHP_EOL . "     */"],
            'Required Singular Number and String Array' => [new DecodedObject('StringArray', [new XmlObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING, ParameterType::INTEGER])]), "/**" . PHP_EOL . "     * @param string[] \$stringArray" . PHP_EOL . "     */"],
            'Required Singular Object Array' => [new DecodedObject('ObjectArray', [new XmlObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter])])]), "/**" . PHP_EOL . "     * @param Object[] \$objectArray" . PHP_EOL . "     */"],
            'Required Singular Mixed Array' => [new DecodedObject('MixedArray', [new XmlObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN])]), "/**" . PHP_EOL . "     * @param Object[] \$mixedArray" . PHP_EOL . "     */"],
            'Optional Singular Int Array' => [new DecodedObject('IntArray', [new XmlObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?int[] \$intArray" . PHP_EOL . "     */"],
            'Optional Singular Float Array' => [new DecodedObject('FloatArray', [new XmlObjectParameter('floatArray', 'floatArray', [ParameterType::ARRAY], [ParameterType::FLOAT, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?float[] \$floatArray" . PHP_EOL . "     */"],
            'Optional Singular String Array' => [new DecodedObject('StringArray', [new XmlObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?string[] \$stringArray" . PHP_EOL . "     */"],
            'Optional Singular Object Array' => [new DecodedObject('ObjectArray', [new XmlObjectParameter('objectArray', 'objectArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?Object[] \$objectArray" . PHP_EOL . "     */"],
            'Optional Singular Mixed Array' => [new DecodedObject('MixedArray', [new XmlObjectParameter('mixedArray', 'mixedArray', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter]), ParameterType::STRING, ParameterType::INTEGER, ParameterType::FLOAT, ParameterType::BOOLEAN, ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?Object[] \$mixedArray" . PHP_EOL . "     */"],
            'Multi Array' => [new DecodedObject('IntArray', [new XmlObjectParameter('intArray', 'intArray', [ParameterType::ARRAY], [ParameterType::INTEGER]), new XmlObjectParameter('stringArray', 'stringArray', [ParameterType::ARRAY], [ParameterType::STRING])]), "/**" . PHP_EOL . "     * @param int[] \$intArray" . PHP_EOL . "     * @param string[] \$stringArray" . PHP_EOL . "     */"],
            'Empty Array' => [new DecodedObject('EmptyArray', [new XmlObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [])]), ''],
            'Null Array' => [new DecodedObject('EmptyArray', [new XmlObjectParameter('emptyArray', 'emptyArray', [ParameterType::ARRAY], [ParameterType::NULL])]), "/**" . PHP_EOL . "     * @param ?string[] \$emptyArray" . PHP_EOL . "     */"],
        ];
    }

    #[DataProvider('parameterHydrationProvider')]
    public function testGenerateParameterHydration(DecodedObject $object, string $expectedParameterHydration): void
    {
        $service = $this->createService();
        $result = $service->generateHydrationLogic($object);

        self::assertSame($expectedParameterHydration, $result);
    }

    public static function parameterHydrationProvider(): array
    {
        $stringParameter = new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new XmlObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new XmlObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $objectParameter = new XmlObjectParameter('objectType', 'objectType', [ParameterType::OBJECT], [], false, new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new XmlObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'Singular Standard Type' => [new DecodedObject('String', [$stringParameter]), "stringType: \$data->stringType->__toString()," . PHP_EOL],
            'Singular Standard Type Special Name .' => [new DecodedObject('String', [new XmlObjectParameter('string.Type', 'stringType', [ParameterType::STRING])]), "stringType: \$data->{'string.Type'}->__toString()," . PHP_EOL],
            'Singular Standard Type Special Name :' => [new DecodedObject('String', [new XmlObjectParameter('string:Type', 'stringType', [ParameterType::STRING])]), "stringType: \$data->{'string:Type'}->__toString()," . PHP_EOL],
            'Singular Standard Type Special Name -' => [new DecodedObject('String', [new XmlObjectParameter('string-Type', 'stringType', [ParameterType::STRING])]), "stringType: \$data->{'string-Type'}->__toString()," . PHP_EOL],
            'Singular Standard String Type - Attribute' => [new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING], [],true)]), "stringType: \$data['stringType']," . PHP_EOL],
            'Singular Standard Non Init Type - Attribute' => [new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::INTEGER], [],true)]), "stringType: (int) \$data['stringType']," . PHP_EOL],
            'Singular Object Type' => [new DecodedObject('Object', [$objectParameter]), "objectType: Object::hydrate(\$data->objectType)," . PHP_EOL],
            'Singular Array Standard Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY])]), "arrayType: array_map('strval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Array Standard Type - Float' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::FLOAT])]), "arrayType: array_map('floatval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Array Standard Type - Int' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::INTEGER])]), "arrayType: array_map('intval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Array Standard Type - String and Int' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::FLOAT, ParameterType::STRING])]), "arrayType: array_map('strval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Array Standard Type - String and Float' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::INTEGER, ParameterType::STRING])]), "arrayType: array_map('strval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Array Object Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [new DecodedObject('Object', [$stringParameter])])]), "arrayType: Object::hydrateMany(iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Nullable Standard Type' => [new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]), "stringType: (string)\$data->stringType !== '' ? \$data->stringType->__toString() : null," . PHP_EOL],
            'Singular Nullable Standard Type - Attribute' => [new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL], [],true)]), "stringType: isset(\$data['stringType']) ? \$data['stringType'] : null," . PHP_EOL],
            'Singular Nullable Object Type' => [new DecodedObject('Object', [new XmlObjectParameter('objectType', 'objectType', [ParameterType::OBJECT, ParameterType::NULL], [], false, new DecodedObject('Object', [$stringParameter]))]), "objectType: (string)\$data->objectType !== '' ? Object::hydrate(\$data->objectType) : null," . PHP_EOL],
            'Singular Nullable Array Standard Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL])]), "arrayType: array_map('strval', iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Singular Nullable Array Object Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL], [new DecodedObject('Object', [$stringParameter])])]), "arrayType: Object::hydrateMany(iterator_to_array(\$data->arrayType, false))," . PHP_EOL],
            'Multi Types' => [new DecodedObject('NoDoc', [$stringParameter, $integerParameter, $floatParameter, $booleanParameter, $objectParameter, $nullParameter]), "stringType: \$data->stringType->__toString()," . PHP_EOL . "intType: (int) \$data->intType->__toString()," . PHP_EOL . "floatType: (float) \$data->floatType->__toString()," . PHP_EOL . "booleanType: (bool) \$data->booleanType->__toString()," . PHP_EOL . "objectType: Object::hydrate(\$data->objectType)," . PHP_EOL . "nullType: (string)\$data->nullType !== '' ? \$data->nullType->__toString() : null," . PHP_EOL],
        ];
    }

    #[DataProvider('parameterValidationProvider')]
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
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING])]),
                "if ((string)\$data->stringType === '') {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Multiple Required' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING]), new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER])]),
                "if ((string)\$data->stringType === '' || " . PHP_EOL . "(string)\$data->intType === '') {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Single Required Attribute' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING], [], true)]),
                "if (!isset(\$data['stringType'])) {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Multiple Required Attributes' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING], [], true), new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER], [], true)]),
                "if (!isset(\$data['stringType'], \$data['intType'])) {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Required Attribute and Value' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING], [], true), new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER], [])]),
                "if (!isset(\$data['stringType']) || " . PHP_EOL . "(string)\$data->intType === '') {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Mixed Parameters' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING]), new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER, ParameterType::NULL])]),
                "if ((string)\$data->stringType === '') {" . PHP_EOL . "\t\tthrow new \RuntimeException('Missing required parameter');" . PHP_EOL . "}" . PHP_EOL,
            ],
            'Single Nullable' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]),
                '',
            ],
            'Single Nullable Attribute' => [
                new DecodedObject('String', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL], [], true)]),
                '',
            ],
        ];
    }

    #[DataProvider('parameterProvider')]
    public function testGenerateParameter(DecodedObject $object, string $expectedParameter): void
    {
        $service = $this->createService();
        $result = $service->generateParameters($object);

        self::assertSame($expectedParameter, $result);
    }

    public static function parameterProvider(): array
    {
        $stringParameter = new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING]);
        $integerParameter = new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER]);
        $floatParameter = new XmlObjectParameter('floatType', 'floatType', [ParameterType::FLOAT]);
        $booleanParameter = new XmlObjectParameter('booleanType', 'booleanType', [ParameterType::BOOLEAN]);
        $XmlObjectParameter = new XmlObjectParameter('objectType', 'objectType', [ParameterType::OBJECT], [], false, new DecodedObject('Object', [$stringParameter]));
        $nullParameter = new XmlObjectParameter('nullType', 'nullType', [ParameterType::NULL]);

        return [
            'Singular Required String Type' => [new DecodedObject('String', [$stringParameter]), "public string \$stringType," . PHP_EOL],
            'Singular Required Int Type' => [new DecodedObject('Int', [$integerParameter]), "public int \$intType," . PHP_EOL],
            'Singular Required Float Type' => [new DecodedObject('Float', [$floatParameter]), "public float \$floatType," . PHP_EOL],
            'Singular Required Boolean Type' => [new DecodedObject('Bool', [$booleanParameter]), "public bool \$booleanType," . PHP_EOL],
            'Singular Required Object Type' => [new DecodedObject('Object', [$XmlObjectParameter]), "public Object \$objectType," . PHP_EOL],
            'Singular Required Array Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY], [ParameterType::STRING])]), "public array \$arrayType," . PHP_EOL],
            'Singular Required Multi Standard Type' => [new DecodedObject('Multi', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::INTEGER])]), "public string \$stringType," . PHP_EOL],
            'Singular Required Multi Number Type' => [new DecodedObject('Multi', [new XmlObjectParameter('numberType', 'numberType', [ParameterType::INTEGER, ParameterType::FLOAT])]), "public float \$numberType," . PHP_EOL],
            'Singular Required Multi Object Type' => [new DecodedObject('Multi', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::OBJECT], [], false, new DecodedObject('Object', [$stringParameter]))]), "public Object \$stringType," . PHP_EOL],
            'Singular Nullable String Type' => [new DecodedObject('Multi', [new XmlObjectParameter('stringType', 'stringType', [ParameterType::STRING, ParameterType::NULL])]), "public ?string \$stringType = null," . PHP_EOL],
            'Singular Nullable Int Type' => [new DecodedObject('Multi', [new XmlObjectParameter('intType', 'intType', [ParameterType::INTEGER, ParameterType::NULL])]), "public ?int \$intType = null," . PHP_EOL],
            'Singular Nullable Float Type' => [new DecodedObject('Multi', [new XmlObjectParameter('floatType', 'floatType', [ParameterType::FLOAT, ParameterType::NULL])]), "public ?float \$floatType = null," . PHP_EOL],
            'Singular Nullable Bool Type' => [new DecodedObject('Multi', [new XmlObjectParameter('boolType', 'boolType', [ParameterType::BOOLEAN, ParameterType::NULL])]), "public ?bool \$boolType = null," . PHP_EOL],
            'Singular Nullable Type' => [new DecodedObject('Multi', [new XmlObjectParameter('nullType', 'nullType', [ParameterType::NULL])]), "public ?string \$nullType = null," . PHP_EOL],
            'Singular Nullable Object Type' => [new DecodedObject('Multi', [new XmlObjectParameter('objType', 'objType', [ParameterType::OBJECT, ParameterType::NULL], [], false, new DecodedObject('Object', [$stringParameter]))]), "public ?Object \$objType = null," . PHP_EOL],
            'Singular Nullable Array Type' => [new DecodedObject('Array', [new XmlObjectParameter('arrayType', 'arrayType', [ParameterType::ARRAY, ParameterType::NULL], [ParameterType::STRING])]), "public ?array \$arrayType = []," . PHP_EOL],
            'Singular Nullable Multi Standard Type' => [new DecodedObject('Multi', [new XmlObjectParameter('intStringType', 'intStringType', [ParameterType::STRING, ParameterType::INTEGER, ParameterType::NULL])]), "public ?string \$intStringType = null," . PHP_EOL],
            'Multi Row Required String and Int' => [new DecodedObject('String', [$stringParameter, $integerParameter]), "public string \$stringType," . PHP_EOL . "public int \$intType," . PHP_EOL],
        ];
    }

    private function createService(): XmlDecodedObjectService
    {
        return new XmlDecodedObjectService();
    }
}