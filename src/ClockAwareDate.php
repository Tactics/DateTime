<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;

final class ClockAwareDate implements ClockAwareInterface
{
    private function __construct(
        protected Date $date,
        protected readonly ?ClockInterface $clock = null
    ) {
    }

    public static function from(
        DateTimeInterface $dateTime,
        ?ClockInterface $clock = null
    ): ClockAwareDate {
        $date = Date::from(
            dateTime: $dateTime
        );
        return new ClockAwareDate(
            date: $date,
            clock: $clock
        );
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now() ?:
            (new NativeClock($this->date()->toDateTime()->getTimezone()))->now();
    }

    public function date(): DateInterface
    {
        return $this->date;
    }

    public function isFuture(): bool
    {
        return $this->date()->isAfter($this->now());
    }

    public function isPast(): bool
    {
        return $this->date()->isBefore($this->now());
    }

    public function add($years = 0, $months = 0, $days = 0): ClockAwareDate
    {
        $sum = $this->date->add($years, $months, $days);
        return new ClockAwareDate($sum, $this->clock);
    }
}
