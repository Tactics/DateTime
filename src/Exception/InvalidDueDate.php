<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidDueDate extends LogicException
{

    public const IN_PAST = 1;

    public static function inPast(): self
    {
        return new self(
            'A due date can only be in the future',
            self::IN_PAST
        );
    }
}
