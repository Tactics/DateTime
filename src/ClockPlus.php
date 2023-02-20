<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;

final class ClockPlus implements ClockPlusInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public static function create(
        ?ClockInterface $clock = null,
    ): ClockPlus {
        return new ClockPlus(
            clock: $clock ?: new NativeClock(),
        );
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }

    public function nowPlus(): DateTimePlus
    {
        return DateTimePlus::from(
            raw: $this->now()->format(FormatWithTimezone::ATOM->pattern()),
            format: FormatWithTimezone::ATOM
        );
    }
}
