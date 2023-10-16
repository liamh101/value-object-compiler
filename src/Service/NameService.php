<?php

namespace LiamH\Valueobjectgenerator\Service;

class NameService
{
    public function createClassName(string $name): string
    {
        return $this->createName($name);
    }

    public function createVariableName(string $name): string
    {
        return lcfirst($this->createName($name));
    }

    private function createName(string $name): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $name));

        return implode('', array_map(static fn ($word) => ucfirst($word), $words));
    }
}