<?php

namespace LiamH\Valueobjectgenerator\Exception;

use Exception;

class ObjectReducerException extends Exception
{
    public static function invalidAmount(int $minimumAmount): self
    {
        return new ObjectReducerException('Not enough Objects to reduce, minimum of ' . $minimumAmount . ' items required');
    }

    public static function invalidReduceType(mixed $foundType): self
    {
        $type = gettype($foundType);

        if (is_object($foundType)) {
            $type = get_class($foundType);
        }

        return new ObjectReducerException('Object reducer requires DecodedObject. ' . $type . ' passed');
    }

}