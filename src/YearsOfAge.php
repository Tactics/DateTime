<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeInterface;
use InvalidArgumentException;

final class YearsOfAge
{
    private readonly int $years;
    private readonly int $months;

    private function __construct(int $months)
    {
        if ($months < 0) {
            throw new InvalidArgumentException('A year of age can only be a positive number');
        }

        $this->months = $months;
        $this->years = (int) floor($months / 12);
    }

    public static function on(DateTimeInterface $dateTime, DayOfBirth $dayOfBirth): YearsOfAge
    {
        if ($dayOfBirth->isAfter($dateTime)) {
            return new self(0);
        }

        $diff = $dayOfBirth->toDateTime()->diff($dateTime);
        return new self($diff->m);
    }

    public static function from(int $years, int $andXMonths = 0): YearsOfAge
    {
        return new self(($years * 12) + $andXMonths);
    }

    public function inYears(): int
    {
        return $this->years;
    }

    public function inMonths(): int
    {
        return $this->months;
    }
}
