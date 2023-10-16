<?php

namespace Service;

use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Service\NameService;
use PHPUnit\Framework\TestCase;

class NameServiceTest extends TestCase
{
    /**
     * @dataProvider classNameProvider
     */
    public function testCreateName(string $passedName, string $expectedResult): void
    {
        $reflection = new \ReflectionClass(NameService::class);
        $method = $reflection->getMethod('createName');
        $method->setAccessible(true);

        $generator = new NameService();

        $result = $method->invokeArgs($generator, [$passedName]);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider classNameProvider
     */
    public function testCreateClassName(string $passedName, string $expectedResult): void
    {
        $service = new NameService();

        $result = $service->createClassName($passedName);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider variableNameProvider
     */
    public function testCreateVariableName(string $passedName, string $expectedResult): void
    {
        $service = new NameService();
        $result = $service->createVariableName($passedName);

        self::assertSame($expectedResult, $result);
    }

    public static function classNameProvider(): array
    {
        return [
            ['test_name', 'TestName'],
            ['test-name', 'TestName'],
            ['test name', 'TestName'],
            ['testname', 'Testname'],
            ['TestName', 'TestName'],
        ];
    }

    public static function variableNameProvider(): array
    {
        return [
            ['test_name', 'testName'],
            ['test-name', 'testName'],
            ['test name', 'testName'],
            ['testname', 'testname'],
            ['TestName', 'testName'],
        ];
    }
}