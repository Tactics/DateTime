<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
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

    public function isSameDay(DateTimeInterface $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isSameDay($dateTime);
    }

    public function isBefore(DateTimeInterface $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isBefore($dateTime);
    }

    public function isAfter(DateTimeInterface $dateTime): bool
    {
        return $this->date->asDateTimePlus()->isAfter($dateTime);
    }
}
