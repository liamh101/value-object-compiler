<?php

namespace Console\Command;

use LiamH\Valueobjectgenerator\Console\Command\GenerateFromJson;
use LiamH\Valueobjectgenerator\Factory\GeneratorCommandFactory;
use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFromJsonTest extends TestCase
{
    public function testCommand(): void
    {
        $reflection = new \ReflectionClass(GenerateFromJson::class);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $factoryMock = $this->createMockFactory();

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getArgument')
            ->with('sourceFile')
            ->willReturn('SourceFile.json');

        $outputInterface = $this->createMock(OutputInterface::class);
        $outputInterface->expects($this->exactly(2))
            ->method('writeln');

        $command = new GenerateFromJson(null, $factoryMock);

        $result = $method->invokeArgs($command, [$inputInterface, $outputInterface]);

        self::assertSame(0, $result);
    }

    public function testCommandInvalidFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $reflection = new \ReflectionClass(GenerateFromJson::class);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $factoryMock = $this->createMock(GeneratorCommandFactory::class);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getArgument')
            ->with('sourceFile')
            ->willReturn(null);

        $outputInterface = $this->createMock(OutputInterface::class);

        $command = new GenerateFromJson(null, $factoryMock);

        $result = $method->invokeArgs($command, [$inputInterface, $outputInterface]);

        self::assertSame(0, $result);
    }

    private function createMockFactory(): GeneratorCommandFactory
    {
        $factory = $this->createMock(GeneratorCommandFactory::class);
        $factory->expects($this->once())->method('createSourceGenerator')->willReturn($this->createSourceGeneratorMock());
        $factory->expects($this->once())->method('createFileGenerator')->willReturn($this->createFileGeneratorMock());
        $factory->expects($this->once())->method('createFileService')->willReturn($this->createFileServiceMock());

        return $factory;
    }

    private function createSourceGeneratorMock(): JsonGenerator
    {
        $factory = $this->createMock(JsonGenerator::class);
        $factory->expects($this->once())
            ->method('generateClassFromSource')
            ->with('SourceFile', '{"Contents": "Hello World"}')
            ->willReturn(new DecodedObject('SourceFile', []));

        return $factory;
    }

    private function createFileGeneratorMock(): ValueObjectGenerator
    {
        $factory = $this->createMock(ValueObjectGenerator::class);
        $factory
            ->expects($this->once())
            ->method('createFiles')
            ->willReturn(true);

        return $factory;
    }

    public function createFileServiceMock(): FileService
    {
        $filePath = 'SourceFile.json';

        $factory = $this->createMock(FileService::class);
        $factory
            ->expects($this->once())
            ->method('getFileContentsFromPath')
            ->with($filePath)
            ->willReturn('{"Contents": "Hello World"}');
        $factory
            ->expects($this->once())
            ->method('getFileNameFromPath')
            ->with($filePath)
            ->willReturn('SourceFile');

        return $factory;
    }
}