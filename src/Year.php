<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * AD denotes the calendar era after the birth of Jesus Christ
 * https://en.wikipedia.org/wiki/Anno_Domini
 * https://en.wikipedia.org/wiki/ISO_8601
 *
 * 0000 - 9999 and more ...
 *
 * -0001 = 2BC
 * 0000 = 1BC
 * 0001 = 1AD
 */
final class Year implements YearInterface
{
    private function __construct(
        private readonly int $year
    ) {
        if (strlen((string)$this->year) >= 4) {
            throw new InvalidArgumentException('A year must be represented as a minimum of 4 digets.');
        }

        if ($this->year < 0) {
            throw new InvalidArgumentException('A year after the birth of christ can only be a positive number.');
        }

        if ($this->year === 0) {
            throw new InvalidArgumentException('A year after the birth of christ can not be zero. the year AD 1 immediately follows the year 1 BC.');
        }
    }

    public static function for(int $year): Year
    {
        return new Year($year);
    }

    public function toInt(): int
    {
        return $this->year;
    }

    public function next(): YearInterface
    {
        return new Year($this->year + 1);
    }

    public function previous(): YearInterface
    {
        if ($this->year === 1) {
            return YearBC::for($this->year - 1);
        }
        return new Year($this->year - 1);
    }

    public function firstDay(): DateTimeImmutable
    {
        $year = (string) $this->year;
        if (strlen($year) < 4) {
            $padded = str_pad($year, 4, '0', STR_PAD_LEFT);
        }
        return new DateTimeImmutable('-' . $padded . '-01-01');
    }

    public function lastDay(): DateTimeImmutable
    {
        $year = (string) $this->year;
        if (strlen($year) < 4) {
            $padded = str_pad($year, 4, '0', STR_PAD_LEFT);
        }
        return new DateTimeImmutable('-' . $padded . '-12-31');
    }
}
