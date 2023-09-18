<?php

namespace LiamH\Valueobjectgenerator\ValueObject;

readonly class DecodedObject
{

    /**
     * @param string $name
     * @param ObjectParameter[] $parameters
     */
    public function __construct(
        public string $name,
        public array  $parameters,
    ) {
    }

}