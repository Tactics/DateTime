<?php

namespace Tactics\DateTime\Exception;

use LogicException;

class InvalidDateTimePlus extends LogicException
{
    public const INVALID_DATE = 1;

    public const NOT_STRICTLY_VALID_DATE = 2;

    public static function invalidDate(string $raw): self
    {
        return new self(
            sprintf('A date can only be created from a valid format and string combination, %s is invalid', $raw),
            self::INVALID_DATE
        );
    }

    public static function notStrictlyValid(array $errors, string $raw): self
    {
        return new self(
            sprintf(
                'A date can only be created from a strictly valid DateTimeImmutable. %s is invalid.
                    The passed DateTime contained %s warnings : %s and %s errors : %s ',
                $raw,
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
