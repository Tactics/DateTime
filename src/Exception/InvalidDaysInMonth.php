<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidDaysInMonth extends LogicException
{
    public const NOT_IN_SCOPE = 1;

    public static function notInScope(): self
    {
        return new self(
            'The number of days in a month can only be between 28 and 31',
            self::NOT_IN_SCOPE
        );
    }
}
