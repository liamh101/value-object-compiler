<?php

namespace LiamH\ValueObjectCompiler\Test;

readonly class Catalog
{
    /**
     * @param Book[] $book
     */
    public function __construct(
        public array $book,
    ) {
    }

    /**
     * @return self[]
     */
    public static function hydrateMany(array $bulkData): array
    {
        $result = [];

        foreach ($bulkData as $data) {
            $result[] = self::hydrate($data);
        }

        return $result;
    }

    public static function hydrate(\SimpleXMLElement $data): self
    {
        if ((string)$data->book === '') {
            throw new \RuntimeException('Missing required parameter');
        }
        return new self(book: Book::hydrateMany(iterator_to_array($data->book, false)),);
    }

    public function toSource(): array
    {
        return (array)$this;
    }
}
