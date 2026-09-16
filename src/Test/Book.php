<?php

namespace LiamH\ValueObjectCompiler\Test;

readonly class Book
{
    public function __construct(
        public string $author,
        public string $description,
        public string $genre,
        public string $id,
        public float $price,
        public string $publishDate,
        public string $title,
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
        if (
            !isset($data['id']) ||
            (string)$data->author === '' ||
            (string)$data->description === '' ||
            (string)$data->genre === '' ||
            (string)$data->price === '' ||
            (string)$data->publish_date === '' ||
            (string)$data->title === ''
        ) {
            throw new \RuntimeException('Missing required parameter');
        }
        return new self(
            author: $data->author->__toString(),
            description: $data->description->__toString(),
            genre: $data->genre->__toString(),
            id: $data['id'],
            price: (float)$data->price->__toString(),
            publishDate: $data->publish_date->__toString(),
            title: $data->title->__toString(),
        );
    }

    public function toSource(): array
    {
        return (array)$this;
    }
}
