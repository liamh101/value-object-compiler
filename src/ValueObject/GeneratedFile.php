<?php

namespace LiamH\ValueObjectCompiler\ValueObject;

use LiamH\ValueObjectCompiler\Enum\FileExtension;

readonly class GeneratedFile
{
    public function __construct(
        public string $name,
        public string $contents,
        public FileExtension $extension,
    ) {
    }

    public function getFullFileName(): string
    {
        return $this->name . $this->extension->value;
    }
}
