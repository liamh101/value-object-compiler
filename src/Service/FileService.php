<?php

namespace LiamH\Valueobjectgenerator\Service;

use LiamH\Valueobjectgenerator\Exception\FileException;
use LiamH\Valueobjectgenerator\ValueObject\DecodedObject;

class FileService
{
    private const STUB_LOCATION = 'src/Stub/';

    /** @var string[] */
    private array $cacheFiles = [];

    public function populateValueObjectFile(DecodedObject $object): string
    {
        return str_replace(
            [
                '{{ClassName}}',
                '{{Docblock}}',
                '{{Parameters}}',
            ],
            [
                $object->name,
                $object->generateDocblock(),
                $object->generateParameters(),
            ],
            $this->getValueObjectFile()
        );
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
}
