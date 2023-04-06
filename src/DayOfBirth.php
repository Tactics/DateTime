<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateTimeInterface;
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

    public function is(YearsOfAge $age, DateTimePlus $on): bool
    {
        $whenAge = $this->when($age);
        $toCarbon = (new Carbon($whenAge->toPhpDateTime()))->startOfDay();
        $onToCarbon = (new Carbon($on->toPhpDateTime()))->startOfDay();

        return $toCarbon->isSameDay($onToCarbon) || $toCarbon->isBefore($onToCarbon);
    }

    public function when(YearsOfAge $age): DateTimePlus
    {
        return $this->date->add(months: $age->inMonths())->asDateTimePlus();
    }
    public function toDateTimePlus(): DateTimePlus
    {
        return $this->date->asDateTimePlus();
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
