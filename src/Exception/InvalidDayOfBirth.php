<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidDayOfBirth extends LogicException
{

    public const IN_FUTURE = 1;

    public static function inFuture(): self
    {
        return new self(
            'A day of birth can not be in the future',
            self::IN_FUTURE
        );
    }
}
