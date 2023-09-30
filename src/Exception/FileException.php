<?php

namespace LiamH\Valueobjectgenerator\Exception;

class FileException extends \Exception
{
    public static function FileNotFound(string $filename): self
    {
        return new static('File ' . $filename . ' could not be found.');
    }
}