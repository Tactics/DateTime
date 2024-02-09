<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidYear extends LogicException
{
    public const NEGATIVE_NUMBER = 1;

    public static function negativeNumber(): self
    {
        return new self(
            'A year AD can only be a positive number, since it start from the year 0',
            self::NEGATIVE_NUMBER
        );
    }
}
