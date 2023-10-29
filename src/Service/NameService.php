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
            return $this->replaceEndOfString('ies', 'y', $name);
        }

        if (str_ends_with($name, 'es') && !str_ends_with($name, 'ues')) {
            return $this->replaceEndOfString('es', '', $name);
        }

        // Ignoring f and fe for the time, due to irregularities in the rule

        return $this->replaceEndOfString('s', '', $name);
    }

    private function replaceEndOfString(string $find, string $replace, string $name): string
    {
        $result = preg_replace('/' . $find . '\Z/', $replace, $name);

        if (!is_string($result)) {
            throw new \Exception('Plural replacement didn\'t return string');
        }

        return $result;
    }

    private function createName(string $name): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $name));

        return implode('', array_map(static fn ($word) => ucfirst($word), $words));
    }
}
