<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Tactics\DateTime\Exception\InvalidMonth;

/*
 * AD denotes the calendar era after the birth of Jesus Christ
 */
final class Month
{
    private function __construct(
        private readonly int $month
    ) {
        if ($this->month < 1 || $this->month > 12) {
            throw InvalidMonth::notInScope();
        }
    }

    public static function from(int $month): Month
    {
        return new self($month);
    }

    public function asInt(): int
    {
        return $this->month;
    }

    public function asString(): string
    {
        return (string) $this->month;
    }
}
