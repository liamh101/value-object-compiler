<?php

namespace LiamH\ValueObjectCompiler\Service;

use LiamH\ValueObjectCompiler\Exception\FileException;
use LiamH\ValueObjectCompiler\ValueObject\DecodedObject;
use LiamH\ValueObjectCompiler\ValueObject\GeneratedFile;
use Symfony\Component\Process\Process;

class FileService
{
    private const STUB_LOCATION = '/../Stub/';
    private const SOURCE_FILE_NAME = 'source.stub';
    private const VALUE_OBJECT_FILE_NAME = 'valueObject.stub';

    /** @var string[] */
    private array $cacheFiles = [];

    public function __construct(
        private readonly string $outputDirectory,
    ) {
        $this->validateOutputDirectory();
    }

    public function populateValueObjectFile(
        string $className,
        string $docblock,
        string $parameters,
        string $hydrationValidation,
        string $hydrationLogic,
        string $hydrationParameter,
        string $toSourceDefinition,
        string $toSourceLogic,
    ): string {
        $valueObjectFile = str_replace(
            '{{IncludeSource}}',
            ($toSourceDefinition || $toSourceLogic) ? $this->getFile(self::SOURCE_FILE_NAME) : '',
            $this->getFile(self::VALUE_OBJECT_FILE_NAME)
        );

        return str_replace(
            [
                '{{ClassName}}',
                '{{Docblock}}',
                '{{Parameters}}',
                '{{HydrationValidation}}',
                '{{HydrationLogic}}',
                '{{HydrationParameter}}',
                '{{ToSourceDefinition}}',
                '{{ToSourceLogic}}',
            ],
            [
                $className,
                $docblock,
                $parameters,
                $hydrationValidation,
                $hydrationLogic,
                $hydrationParameter,
                $toSourceDefinition,
                $toSourceLogic,
            ],
            $valueObjectFile
        );
    }

    public function getFileContentsFromPath(string $path): string
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw FileException::fileNotFound($path);
        }

        return $contents;
    }

    public function getFileNameFromPath(string $path): string
    {
        preg_match('/[\w-]+\./', $path, $matches);

        if (!isset($matches[0])) {
            throw FileException::fileNotFound($path);
        }

        return str_replace('.', '', $matches[0]);
    }

    public function writeFile(GeneratedFile $file): true
    {
        $result = @file_put_contents($this->outputDirectory . $file->getFullFileName(), $file->contents);

        if (!$result) {
            throw FileException::cannotWriteFile($file->name);
        }

        $this->formatFile($file);

        return true;
    }

    private function getFile(string $name): string
    {
        if (isset($this->cacheFiles[$name])) {
            return $this->cacheFiles[$name];
        }

        $contents = file_get_contents(__DIR__ . self::STUB_LOCATION . $name);

        if (!$contents) {
            throw FileException::fileNotFound($name);
        }

        $this->cacheFiles[$name] = $contents;

        return $contents;
    }

    private function formatFile(GeneratedFile $file): true
    {
        $process = new Process(['vendor/bin/phpcbf', '-q', '--standard=PSR12', $this->outputDirectory . $file->getFullFileName()]);
        $process->run();

        // A successful run is deemed unsuccessful
//        if (!$process->isSuccessful()) {
//            throw FileException::couldNotFormatFile($file->name, $process->getOutput());
//        }

        return true;
    }

    private function validateOutputDirectory(): true
    {
        if (!str_ends_with($this->outputDirectory, '/')) {
            throw FileException::invalidOutputDirectory();
        }

        return true;
    }
}
