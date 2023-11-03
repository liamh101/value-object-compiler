# Value Object Compiler

This package takes a source file, such as JSON and creates strict typed, PSR12, readonly Value Objects.

The aim is to slowly support more file formats with the same file output. 

## Installation

## How To Use

### Single JSON file Compiler

`vendor/bin/ValueObjectCompiler compile:json {jsonLocation} --outputDir={dir}`

Single file compiler will take a JSON file and output its value object representation. By default, it will output in the current Directory, but you can specify your own directory using the `outputDir` flag.

#### Example
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
