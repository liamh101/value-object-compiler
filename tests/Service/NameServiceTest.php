<?php

namespace Service;

use LiamH\ValueObjectCompiler\Generator\JsonGenerator;
use LiamH\ValueObjectCompiler\Service\NameService;
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

    /**
     * @dataProvider singularNameProvider
     */
    public function testMakeSingular(string $pluralString, string $expectedSingular): void
    {
        $service = new NameService();
        $result = $service->makeSingular($pluralString);

        self::assertSame($expectedSingular, $result);
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

    public static function singularNameProvider(): array
    {
        return [
            ['buses', 'bus'],
            ['heroes', 'hero'],
            ['echoes', 'echo'],
            ['stories', 'story'],
            ['berries', 'berry'],
            ['animals', 'animal'],
            ['dreams', 'dream'],
            ['cvss', 'cvss'],
            ['issues', 'issue'],
        ];
    }
}