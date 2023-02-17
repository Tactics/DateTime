<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;

final class ClockAwareDateTime implements ClockAwareInterface
{
    private function __construct(
        private DateTimePlus $dateTimePlus,
        private readonly ClockInterface $clock
    ) {
    }

    public static function from(
        DateTimePlus    $dateTimePlus,
        ?ClockInterface $clock = null
    ): ClockAwareDateTime {
        return new ClockAwareDateTime(
            dateTimePlus: $dateTimePlus,
            clock: $clock ?: new NativeClock($dateTimePlus->toPhpDateTime()->getTimezone())
        );
    }

    /**
     * Method added to support generic php ClockInterface.
     * for all logic not related to the ClockInterface use 'moment'.
     * @return DateTimeImmutable
     */
    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }

    public function nowAsDateTimePlus(): DateTimePlus
    {
        return DateTimePlus::from(
            $this->now()->format(FormatWithTimezone::ATOM->value),
            FormatWithTimezone::ATOM,
        );
    }

    public function asDateTimePlus(): DateTimePlusInterface
    {
        return $this->dateTimePlus;
    }

    public function isFuture(): bool
    {
        return $this->asDateTimePlus()->isAfter($this->nowAsDateTimePlus()->toPhpDateTime());
    }

    public function isPast(): bool
    {
        return $this->asDateTimePlus()->isBefore($this->nowAsDateTimePlus()->toPhpDateTime());
    }

    public function add($years = 0, $months = 0, $days = 0): ClockAwareDateTime
    {
        $sum = $this->asDateTimePlus()->add($years, $months, $days);
        return new ClockAwareDateTime($sum, $this->clock);
    }
}
