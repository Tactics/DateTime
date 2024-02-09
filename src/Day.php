<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Tactics\DateTime\Exception\InvalidDay;

final class Day
{
    private function __construct(
        private readonly int $day
    )
    {
        if ($this->day < 1 || $this->day > 31) {
            throw InvalidDay::notInScope();
        }
    }

    public static function from(int $day): Day
    {
        return new self($day);
    }

    public function asInt(): int
    {
        return $this->day;
    }

    public function asString(): string
    {
        return (string) $this->day;
    }

}
