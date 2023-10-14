<?php

namespace Enum;

use LiamH\Valueobjectgenerator\Enum\ParameterType;
use PHPUnit\Framework\TestCase;

class ParameterTypeTest extends TestCase
{

    /**
     * @dataProvider parameterProvider
     */
    public function testDefinitionName(ParameterType $type, string $expectedDefinition): void
    {
        self::assertSame($expectedDefinition, $type->getDefinitionName());
    }

    public static function parameterProvider(): array
    {
        return [
            'string' => [ParameterType::STRING, 'string'],
            'integer' => [ParameterType::INTEGER, 'int'],
            'float' => [ParameterType::FLOAT, 'float'],
            'array' => [ParameterType::ARRAY, 'array'],
            'object' => [ParameterType::OBJECT, ''],
            'null' => [ParameterType::NULL, '?'],
            'boolean' => [ParameterType::BOOLEAN, 'bool'],
        ];
    }
}