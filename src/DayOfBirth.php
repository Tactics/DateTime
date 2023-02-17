<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Tactics\DateTime\Exception\InvalidDayOfBirth;

final class DayOfBirth
{
    protected function __construct(
        protected ClockAwareDateTime $date,
    ) {
        if ($this->date->isFuture()) {
            throw InvalidDayOfBirth::inFuture();
        }
    }

    public static function from(
        ClockAwareDateTime $dateTime
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
        return $this->date->add(months: $age->inMonths())->asDateTimePlus()->toPhpDateTime();
    }

    public function toDateTime(): DateTimeImmutable
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
