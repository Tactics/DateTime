<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidYear;
use Tactics\DateTime\Exception\InvalidYearsOfAge;

/**
 * This class assumes an AD year, for BC year another class is needed.
 * AD denotes the calendar era after the birth of Jesus Christ
 */
final class Year
{
    private readonly int $year;

    private function __construct(int $year)
    {
        if ($year < 0) {
            throw InvalidYear::negativeNumber();
        }

        $this->year = $year;
    }

    public static function from(int $year): Year
    {
        return new self($year);
    }

    public function asInt(): int
    {
        return $this->year;
    }

    public function asString(): string
    {
        return (string) $this->year;
    }

    public function firstDay(): DateTimePlus
    {
        $year = str_pad($this->asString(), 4, '0', STR_PAD_LEFT);
        return DateTimePlus::from($year . '-01-01T00:00:00+00:00', FormatWithTimezone::ATOM);
    }

    public function lastDay(): DateTimePlus
    {
        $year = str_pad($this->asString(), 4, '0', STR_PAD_LEFT);
        return DateTimePlus::from($year . '-12-31T00:00:00+00:00', FormatWithTimezone::ATOM);
    }
}
