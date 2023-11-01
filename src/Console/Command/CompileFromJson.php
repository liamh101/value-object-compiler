<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use LiamH\Valueobjectgenerator\Factory\JsonGeneratorCommandFactory;
use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\JsonDecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use LiamH\Valueobjectgenerator\Service\NameService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'compile:json', description: 'Compile Value Objects from a JSON file')]
class CompileFromJson extends Command
{
    private const DEFAULT_OUTPUT_DIR = './';
    private readonly JsonGeneratorCommandFactory $factory;

    private JsonGenerator $jsonGenerator;
    private ValueObjectGenerator $valueObjectGenerator;
    private FileService $fileService;

    private string $outputDir;

    public function __construct(string $name = null, JsonGeneratorCommandFactory $factory)
    {
        $this->factory = $factory;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(name: 'sourceFile', description: 'path to file to be scanned')
            ->addOption(name: 'outputDir', mode: InputOption::VALUE_REQUIRED, description: 'Where compiled Value Objects are written to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputDir = $this->getOutputDirectory($input);

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

    private function getOutputDirectory(InputInterface $input): string
    {
        $dir = $input->getOption('outputDir');

        if (!is_string($dir) || $dir === '') {
            return self::DEFAULT_OUTPUT_DIR;
        }

        if (!str_ends_with($dir, '/')) {
            $dir .= '/';
        }

        return $dir;
    }

    private function createServices(): void
    {
        $this->jsonGenerator = $this->factory->createSourceGenerator();
        $this->valueObjectGenerator = $this->factory->createFileGenerator($this->outputDir);
        $this->fileService = $this->factory->createFileService($this->outputDir);
    }
}
