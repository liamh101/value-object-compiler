<?php

namespace LiamH\Valueobjectgenerator\Service;

use LiamH\Valueobjectgenerator\Exception\FileException;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;
use LiamH\Valueobjectgenerator\ValueObject\GeneratedFile;
use Symfony\Component\Process\Process;

class FileService
{
    private const STUB_LOCATION = 'src/Stub/';

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
    ): string {
        return str_replace(
            [
                '{{ClassName}}',
                '{{Docblock}}',
                '{{Parameters}}',
                '{{HydrationValidation}}',
                '{{HydrationLogic}}'
            ],
            [
                $className,
                $docblock,
                $parameters,
                $hydrationValidation,
                $hydrationLogic,
            ],
            $this->getValueObjectFile()
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

    private function getValueObjectFile(): string
    {
        if (isset($this->cacheFiles['valueObject'])) {
            return $this->cacheFiles['valueObject'];
        }

        $contents = file_get_contents(self::STUB_LOCATION . 'valueObject.stub');

        if (!$contents) {
            throw FileException::fileNotFound('valueObject.stub');
        }

        $this->cacheFiles['valueObject'] = $contents;

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
