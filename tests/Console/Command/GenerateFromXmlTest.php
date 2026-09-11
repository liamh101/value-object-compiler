<?php

namespace Console\Command;

use LiamH\ValueObjectCompiler\Console\Command\CompileFromXml;
use LiamH\ValueObjectCompiler\Factory\XmlGeneratorCommandFactory;
use LiamH\ValueObjectCompiler\Generator\ValueObjectGenerator;
use LiamH\ValueObjectCompiler\Generator\XmlGenerator;
use LiamH\ValueObjectCompiler\Service\FileService;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFromXmlTest extends TestCase
{
    public function testCommand(): void
    {
        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $factoryMock = $this->createMockFactory();

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getArgument')
            ->with('sourceFile')
            ->willReturn('SourceFile.xml');

        $outputInterface = $this->createMock(OutputInterface::class);
        $outputInterface->expects($this->exactly(2))
            ->method('writeln');

        $command = new CompileFromXml($factoryMock);

        $result = $method->invokeArgs($command, [$inputInterface, $outputInterface]);

        self::assertSame(0, $result);
    }

    public function testCommandInvalidFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $factoryMock = $this->createMock(XmlGeneratorCommandFactory::class);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getArgument')
            ->with('sourceFile')
            ->willReturn(null);

        $outputInterface = $this->createMock(OutputInterface::class);

        $command = new CompileFromXml($factoryMock);

        $result = $method->invokeArgs($command, [$inputInterface, $outputInterface]);

        self::assertSame(0, $result);
    }

    public function testGetCustomOutputDirectoryDefault(): void
    {
        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('getOutputDirectory');
        $method->setAccessible(true);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getOption')
            ->with('outputDir')
            ->willReturn(null);

        $command = new CompileFromXml(new XmlGeneratorCommandFactory());
        $result = $method->invokeArgs($command, [$inputInterface]);

        self::assertSame('./', $result);
    }

    public function testGetCustomOutputDirectoryEmpty(): void
    {
        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('getOutputDirectory');
        $method->setAccessible(true);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getOption')
            ->with('outputDir')
            ->willReturn('');

        $command = new CompileFromXml(new XmlGeneratorCommandFactory());
        $result = $method->invokeArgs($command, [$inputInterface]);

        self::assertSame('./', $result);
    }

    public function testGetCustomOutputDirectoryProvided(): void
    {
        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('getOutputDirectory');
        $method->setAccessible(true);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getOption')
            ->with('outputDir')
            ->willReturn('/etc/testdir/');

        $command = new CompileFromXml(new XmlGeneratorCommandFactory());
        $result = $method->invokeArgs($command, [$inputInterface]);

        self::assertSame('/etc/testdir/', $result);
    }

    public function testGetCustomOutputDirectoryProvidedMissingSlash(): void
    {
        $reflection = new \ReflectionClass(CompileFromXml::class);
        $method = $reflection->getMethod('getOutputDirectory');
        $method->setAccessible(true);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->once())
            ->method('getOption')
            ->with('outputDir')
            ->willReturn('/etc/testdir');

        $command = new CompileFromXml(new XmlGeneratorCommandFactory());
        $result = $method->invokeArgs($command, [$inputInterface]);

        self::assertSame('/etc/testdir/', $result);
    }

    private function createMockFactory(): XmlGeneratorCommandFactory
    {
        $factory = $this->createMock(XmlGeneratorCommandFactory::class);
        $factory->expects($this->once())->method('createSourceGenerator')->willReturn($this->createSourceGeneratorMock());
        $factory->expects($this->once())->method('createFileGenerator')->willReturn($this->createFileGeneratorMock());
        $factory->expects($this->once())->method('createFileService')->willReturn($this->createFileServiceMock());

        return $factory;
    }

    private function createSourceGeneratorMock(): XmlGenerator
    {
        $factory = $this->createMock(XmlGenerator::class);
        $factory->expects($this->once())
            ->method('generateClassFromSource')
            ->with('SourceFile', '<source><name>Hello World!</name></source>')
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
        $filePath = 'SourceFile.xml';

        $factory = $this->createMock(FileService::class);
        $factory
            ->expects($this->once())
            ->method('getFileContentsFromPath')
            ->with($filePath)
            ->willReturn('<source><name>Hello World!</name></source>');
        $factory
            ->expects($this->once())
            ->method('getFileNameFromPath')
            ->with($filePath)
            ->willReturn('SourceFile');

        return $factory;
    }
}