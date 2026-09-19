<?php

namespace Exception;

use LiamH\ValueObjectCompiler\Exception\FileException;
use PHPUnit\Framework\TestCase;

class FileExceptionTest extends TestCase
{
    public function testFileNotFound(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessageIsOrContains('File TestFile.json could not be found.');

        throw FileException::fileNotFound('TestFile.json');
    }

    public function testCannotWriteFile(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessageIsOrContains('Cannot create file TestFile.php');

        throw FileException::cannotWriteFile('TestFile.php');
    }

    public function testCouldNotFormatFile(): void
    {
        $this->expectException(FileException::class);
        $this->expectExceptionMessageIsOrContains('Could not format file TestFile.php: Invalid PHP');

        throw FileException::couldNotFormatFile('TestFile.php', 'Invalid PHP');
    }
}