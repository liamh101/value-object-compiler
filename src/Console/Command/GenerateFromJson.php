<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use LiamH\Valueobjectgenerator\Factory\GeneratorCommandFactory;
use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\Service\NameService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'generate:json', description: 'Generate Value Objects from a JSON file')]
class GenerateFromJson extends Command
{
    private readonly GeneratorCommandFactory $factory;

    private JsonGenerator $jsonGenerator;
    private ValueObjectGenerator $valueObjectGenerator;
    private FileService $fileService;

    public function __construct(string $name = null, GeneratorCommandFactory $factory)
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

        $contents = file_get_contents($fileLocation);

        if ($contents === false) {
            throw new \RuntimeException('File could not be found!');
        }

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
