<?php

namespace Tactics\DateTime\Exception;

use LogicException;
use Throwable;

class InvalidDateTimePlus extends LogicException {

    public const INVALID_LENGTH = 1;

    public static function invalidDate(): self
    {
        return new self(
            sprintf('A child code can only be %s characters long', ChildCode::LENGTH),
            self::INVALID_LENGTH
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
            self::INVALID_LENGTH
        );
    }





}
