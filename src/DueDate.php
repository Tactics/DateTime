<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use Tactics\DateTime\Exception\InvalidDueDate;

final class DueDate
{
    private function __construct(
        protected ClockAwareDateTime $date,
    ) {
        if (!$this->date->isFuture()) {
            throw InvalidDueDate::inPast();
        }
    }

    public static function from(
        ClockAwareDateTime $dateTime
    ): DueDate {
        return new DueDate($dateTime);
    }

    public function toPhpDateTime(): DateTimeImmutable
    {
        return $this->date->asDateTimePlus()->toPhpDateTime();
    }

    public function isSameDay(DateTimePlus $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isSameDay($dateTime);
    }

    public function isBefore(DateTimePlus $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isBefore($dateTime);
    }

    public function isAfter(DateTimePlus $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isAfter($dateTime);
    }
}
