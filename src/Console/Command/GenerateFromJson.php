<?php

namespace LiamH\Valueobjectgenerator\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'generate:json')]
class GenerateFromJson extends Command
{
    protected function configure(): void
    {
        $this->addArgument(name: 'sourceFile', description: 'path to file to be scanned');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileLocation = $input->getArgument('sourceFile');

        if (!is_string($fileLocation)) {
            throw new \Exception('Source File not defined');
        }

        $contents = file_get_contents($fileLocation);

        if ($contents === false) {
            throw new \Exception('File could not be found!');
        }

        $contents = json_decode($contents, true);


        return Command::SUCCESS;
    }
}
