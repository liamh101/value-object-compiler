<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use LiamH\Valueobjectgenerator\Generator\ValueObjectGenerator;
use LiamH\Valueobjectgenerator\Service\DecodedObjectService;
use LiamH\Valueobjectgenerator\Service\FileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'generate:json', description: 'Generate Value Objects from a JSON file')]
class GenerateFromJson extends Command
{
    private readonly JsonGenerator $jsonGenerator;
    private readonly ValueObjectGenerator $valueObjectGenerator;

    public function __construct(string $name = null)
    {
        $this->jsonGenerator = new JsonGenerator();
        $this->valueObjectGenerator = new ValueObjectGenerator(new DecodedObjectService(), new FileService());
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(name: 'sourceFile', description: 'path to file to be scanned');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileLocation = $input->getArgument('sourceFile');

        if (!is_string($fileLocation)) {
            throw new \RuntimeException('Source File not defined');
        }

        $contents = file_get_contents($fileLocation);

        if ($contents === false) {
            throw new \RuntimeException('File could not be found!');
        }

        $result = $this->jsonGenerator->generateClassFromSource('test', $contents);

        $this->valueObjectGenerator->createFiles($result);

        return Command::SUCCESS;
    }
}
