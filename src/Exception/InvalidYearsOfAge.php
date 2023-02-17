<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidYearsOfAge extends LogicException
{
    public const NEGATIVE_NUMBER = 1;

    public static function negativeNumber(): self
    {
        return new self(
            'A year of age can only be a positive number',
            self::NEGATIVE_NUMBER
        );
    }
}
