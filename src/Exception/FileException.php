<?php

namespace LiamH\Valueobjectgenerator\Exception;

use Exception;

final class FileException extends Exception
{
    public static function fileNotFound(string $filename): self
    {
        return new FileException('File ' . $filename . ' could not be found.');
    }

    public static function cannotWriteFile(string $filename): self
    {
        return new FileException('Cannot create file ' . $filename);
    }
}
