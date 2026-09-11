# Value Object Compiler

[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Coverage Status][badge-coverage]][coverage]
[![Build][badge-build]][build]

This package takes a source file, such as JSON or XML and creates strict typed, PSR12, readonly Value Objects.

The aim is to slowly support more file formats with the same file output. 

## Installation

`composer require --dev liamhackett/valueobjectcompiler`

## How To Use

### Single file Compiler

`vendor/bin/ValueObjectCompiler compile:json {jsonLocation} --outputDir={dir}`
`vendor/bin/ValueObjectCompiler compile:xml {xmlLocation} --outputDir={dir}`


Single file compiler will take a JSON or XML file and output its value object representation. By default, it will output in the current Directory, but you can specify your own directory using the `outputDir` flag.

#### JSON Example
`example.json`
```json 
{
  "id": 12,
  "name": "Json Object",
  "status": "Published",
  "tags": ["tag1", "tag2", "tag3"],
  "sub_objects": [
    {
      "name": "Hello", 
      "value": 13.132
    },
    {
      "name": "World",
      "value": null
    }
  ]
}
```

`Example.php`
```php
<?php

readonly class Example
{
    /**
     * @var string[] $tags
     * @var SubObject[] $subObjects
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public array $subObjects,
        public array $tags,
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

    public static function hydrate(array $data): self
    {
        if (!isset($data['id'], $data['name'], $data['status'], $data['sub_objects'], $data['tags'])) {
            throw new \RuntimeException('Missing required parameter');
        }
        return new self(
            id: $data['id'],
            name: $data['name'],
            status: $data['status'],
            subObjects: SubObject::hydrateMany($data['sub_objects']),
            tags: $data['tags'],
        );
    }
}
```
`SubObject.php`
```php
<?php

readonly class SubObject
{
    public function __construct(
        public string $name,
        public ?float $value = null,
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

    public static function hydrate(array $data): self
    {
        if (!isset($data['name'])) {
            throw new \RuntimeException('Missing required parameter');
        }
        return new self(
            name: $data['name'],
            value: $data['value'] ?? null,
        );
    }
}
```
[badge-source]: https://img.shields.io/badge/source-liamhackett/valueobjectcompiler-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/liamhackett/valueobjectcompiler.svg?style=flat-square&label=release
[badge-license]: https://img.shields.io/packagist/l/liamhackett/valueobjectcompiler.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/liamhackett/valueobjectcompiler.svg?style=flat-square
[badge-coverage]: https://img.shields.io/coveralls/github/liamh101/value-object-compiler/master.svg?style=flat-square
[badge-build]: https://img.shields.io/github/actions/workflow/status/liamh101/value-object-compiler/actions.yml?style=flat-square

[source]: https://github.com/liamh101/value-object-compiler
[release]: https://packagist.org/packages/liamhackett/valueobjectcompiler
[php]: https://php.net
[composer]: http://getcomposer.org/
[conduct]: https://github.com/liamh101/value-object-compiler/blob/master/.github/CODE_OF_CONDUCT.md
[license]: https://github.com/liamh101/value-object-compiler/blob/master/LICENSE
[coverage]: https://coveralls.io/repos/github/liamh101/value-object-compiler?branch=master
[build]: https://github.com/liamh101/value-object-compiler/actions?query=event%3Apush+workflow%3ABuild+branch%3Amaster

#### XML Example
`example.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
    <book id="bk101">
        <author>Example Author</author>
        <title>Sample Title</title>
        <genre>Fiction</genre>
        <price>19.99</price>
        <publish_date>2023-01-01</publish_date>
        <description>A brief description of the book.</description>
    </book>
    <book id="bk102">
        <author>Example Author 2</author>
        <title>Another Sample Title</title>
        <genre>Fiction</genre>
        <price>25.99</price>
        <publish_date>2025-01-01</publish_date>
        <description>A brief description of the book.</description>
    </book>
</catalog>
```

`Catalog.php`
```php
<?php

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
}
```
`Book.php`
```php
<?php

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
            price: (float) $data->price->__toString(),
            publishDate: $data->publish_date->__toString(),
            title: $data->title->__toString(),
        );
    }
}
```
[badge-source]: https://img.shields.io/badge/source-liamhackett/valueobjectcompiler-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/liamhackett/valueobjectcompiler.svg?style=flat-square&label=release
[badge-license]: https://img.shields.io/packagist/l/liamhackett/valueobjectcompiler.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/liamhackett/valueobjectcompiler.svg?style=flat-square
[badge-coverage]: https://img.shields.io/coveralls/github/liamh101/value-object-compiler/master.svg?style=flat-square
[badge-build]: https://img.shields.io/github/actions/workflow/status/liamh101/value-object-compiler/actions.yml?style=flat-square

[source]: https://github.com/liamh101/value-object-compiler
[release]: https://packagist.org/packages/liamhackett/valueobjectcompiler
[php]: https://php.net
[composer]: http://getcomposer.org/
[conduct]: https://github.com/liamh101/value-object-compiler/blob/master/.github/CODE_OF_CONDUCT.md
[license]: https://github.com/liamh101/value-object-compiler/blob/master/LICENSE
[coverage]: https://coveralls.io/repos/github/liamh101/value-object-compiler?branch=master
[build]: https://github.com/liamh101/value-object-compiler/actions?query=event%3Apush+workflow%3ABuild+branch%3Amaster
