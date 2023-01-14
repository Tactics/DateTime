<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * BC denotes the calendar era before the birth of Jesus Christ
 * https://en.wikipedia.org/wiki/Anno_Domini
 * https://en.wikipedia.org/wiki/ISO_8601
 *
 * -0001 = 2BC
 * 0000 = 1BC
 * 0001 = 1AD
 */
final class YearBC implements YearInterface
{

    private function __construct(
        private readonly int $year
    )
    {
        if ($this->year <= 0) {
            throw new InvalidArgumentException('A year before the birth of christ can only be a positive number');
        }
    }

    public static function for(int $year): YearBC
    {
        return new YearBC($year);
    }

    public function toInt(): int
    {
        return $this->year;
    }

    public function next(): YearInterface
    {
        if ($this->year === 0) {
            return Year::for($this->year + 1);
        }
        return new YearBC($this->year + 1);
    }

    public function previous(): YearInterface
    {
        return new YearBC($this->year - 1);
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
