<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidDay extends LogicException
{
    public const NOT_IN_SCOPE = 1;

    public static function notInScope(): self
    {
        return new self(
            'A day can only be a positive number between 1 and 31',
            self::NOT_IN_SCOPE
        );
    }
}
