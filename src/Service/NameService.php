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

    public function makeSingular(string $name): string
    {
        if (!str_ends_with($name, 's') || str_ends_with($name, 'ss')) {
            return $name;
        }

        if (str_ends_with($name, 'ies')) {
            return preg_replace('/ies\Z/', 'y', $name);
        }

        if (str_ends_with($name, 'es')) {
            return preg_replace('/es\Z/', '', $name);
        }

        // Ignoring f and fe for the time, due to irregularities in the rule

        return preg_replace('/s\Z/', '', $name);
    }

    private function createName(string $name): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $name));

        return implode('', array_map(static fn ($word) => ucfirst($word), $words));
    }
}