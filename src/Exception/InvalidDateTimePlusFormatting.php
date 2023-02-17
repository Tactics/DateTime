<?php

namespace Tactics\DateTime\Exception;

use DateTimeZone;
use LogicException;

class InvalidDateTimePlusFormatting extends LogicException
{
    public const FAILED_FORMATTING = 1;

    public static function failedFormatting(
        string $format,
        string $locale,
        DateTimeZone $timezone
    ): self {
        return new self(
            sprintf(
                'The date could not be formatted to %s in %s for timezone %s',
                $format,
                $locale,
                $timezone->getName()
            ),
            self::FAILED_FORMATTING
        );
    }
}
