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


    private function createService(): DecodedObjectService
    {
        return new DecodedObjectService();
    }
}