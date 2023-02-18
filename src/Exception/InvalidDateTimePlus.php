<?php

namespace Tactics\DateTime\Exception;

use LogicException;
use Throwable;

class InvalidDateTimePlus extends LogicException
{
    public const INVALID_DATE = 1;

    public const NOT_STRICTLY_VALID_DATE = 2;

    public static function invalidDate(): self
    {
        return new self(
            'A date can only be created from a valid format and string combination',
            self::INVALID_DATE
        );
    }

    public static function notStrictlyValid(array $errors): self
    {
        return new self(
            sprintf(
                'A date can only be created from a strictly valid DateTimeImmutable.
                    The passed DateTime contained %s warnings : %s and %s errors : %s ',
                $errors['warning_count'],
                implode(
                    separator: ',',
                    array: $errors['warnings']
                ),
                $errors['error_count'],
                implode(
                    separator: ',',
                    array: $errors['errors']
                ),
            ),
            self::NOT_STRICTLY_VALID_DATE
        );
    }
}
