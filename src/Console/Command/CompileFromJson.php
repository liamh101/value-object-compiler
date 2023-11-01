<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use LiamH\Valueobjectgenerator\Factory\JsonGeneratorCommandFactory;
use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\Service\NameService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'compile:json', description: 'Compile Value Objects from a JSON file')]
class CompileFromJson extends Command
{
    private readonly JsonGeneratorCommandFactory $factory;

    private JsonGenerator $jsonGenerator;
    private ValueObjectGenerator $valueObjectGenerator;
    private FileService $fileService;

    public function __construct(string $name = null, JsonGeneratorCommandFactory $factory)
    {
        $this->factory = $factory;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(name: 'sourceFile', description: 'path to file to be scanned');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createServices();

        $fileLocation = $input->getArgument('sourceFile');

        if (!is_string($fileLocation)) {
            throw new \RuntimeException('Source File not defined');
        }

        $contents = $this->fileService->getFileContentsFromPath($fileLocation);

        $output->writeln('Decoding Source File');

        $result = $this->jsonGenerator->generateClassFromSource(
            $this->fileService->getFileNameFromPath($fileLocation),
            $contents
        );

        $output->writeln('Writing to Files');

        $this->valueObjectGenerator->createFiles($result);

        return Command::SUCCESS;
    }

    private function createServices(): void
    {
        $this->jsonGenerator = $this->factory->createSourceGenerator();
        $this->valueObjectGenerator = $this->factory->createFileGenerator();
        $this->fileService = $this->factory->createFileService();
    }
}
