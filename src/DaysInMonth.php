<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Tactics\DateTime\Exception\InvalidDaysInMonth;

final class DaysInMonth
{
    private function __construct(
        private readonly int $days
    ) {
        if ($this->days < 28 || $this->days > 31) {
            throw InvalidDaysInMonth::notInScope();
        }
    }

    public static function from(int $days): DaysInMonth
    {
        return new self($days);
    }

    public function asInt(): int
    {
        return $this->days;
    }

    public function asString(): string
    {
        return (string) $this->days;
    }
}
