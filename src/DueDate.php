<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class DueDate implements DateInterface
{
    private function __construct(
        protected ClockAwareDate $date,
    ) {
        if (!$this->date->isFuture()) {
            throw new InvalidArgumentException('A due date can only be in the future');
        }
    }

    public static function from(
        ClockAwareDate $dateTime
    ): DueDate {
        return new DueDate($dateTime);
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->date->date()->toDateTime();
    }

    public function isSame(DateTimeInterface $dateTime): bool
    {
        return $this->date->date()->isSame($dateTime);
    }

    public function isBefore(DateTimeInterface $dateTime): bool
    {
        return $this->date->date()->isBefore($dateTime);
    }

    public function isAfter(DateTimeInterface $dateTime): bool
    {
        return $this->date->date()->isAfter($dateTime);
    }
}
