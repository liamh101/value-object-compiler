<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use LiamH\Valueobjectgenerator\Generator\JsonGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'generate:json', description: 'Generate Value Objects from a JSON file')]
class GenerateFromJson extends Command
{
    private readonly JsonGenerator $jsonGenerator;

    public function __construct(string $name = null)
    {
        $this->jsonGenerator = new JsonGenerator();
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

        die(var_dump($result));

        return Command::SUCCESS;
    }
}
