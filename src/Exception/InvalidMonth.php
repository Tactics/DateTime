<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidMonth extends LogicException
{
    public const NOT_IN_SCOPE = 1;

    public static function notInScope(): self
    {
        return new self(
            'A month can only be a positive number between 1 and 12',
            self::NOT_IN_SCOPE
        );
    }
}
