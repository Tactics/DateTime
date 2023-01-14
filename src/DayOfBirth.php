<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class DayOfBirth implements DateInterface
{
    protected function __construct(
        protected ClockAwareDate $date,
    ) {
        if ($this->date->isFuture()) {
            throw new InvalidArgumentException('A day of birth can not be in the future');
        }
    }

    public static function from(
        ClockAwareDate $dateTime
    ): DayOfBirth {
        return new DayOfBirth($dateTime);
    }

    public function is(YearsOfAge $age, DateTimeInterface $on): bool
    {
        $whenAge = $this->when($age);
        $toCarbon = (new Carbon($whenAge))->startOfDay();
        $onToCarbon = (new Carbon($on))->startOfDay();

        return $toCarbon->isSameDay($onToCarbon) || $toCarbon->isBefore($onToCarbon);
    }

    public function when(YearsOfAge $age): DateTimeImmutable
    {
        return $this->date->add(months: $age->inMonths())->date()->toDateTime();
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
