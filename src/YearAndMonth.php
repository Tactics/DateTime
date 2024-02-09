<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;

/**
 * This class assumes an AD year, for BC year another class is needed.
 * AD denotes the calendar era after the birth of Jesus Christ
 */
final class YearAndMonth
{
    private function __construct(
        private readonly Year  $year,
        private readonly Month $month
    ) {}

    public static function from(Year $year, Month $month): YearAndMonth
    {
        return new self($year, $month);
    }

    public function year() : Year
    {
        return $this->year;
    }

    public function month() : Month
    {
        return $this->month;
    }

    public function firstDayOfYear() : DateTimePlus
    {
        return $this->year->firstDay();
    }

    public function lastDayOfYear() : DateTimePlus
    {
        return $this->year->lastDay();
    }

    public function firstDayOfMonth() : DateTimePlus
    {
        $year = str_pad($this->year()->asString(), 4, '0', STR_PAD_LEFT);
        $month = str_pad($this->month()->asString(), 2, '0', STR_PAD_LEFT);
        return DateTimePlus::from($year . '-'. $month . '-01T00:00:00+00:00', FormatWithTimezone::ATOM);
    }

    public function lastDayOfMonth() : DateTimePlus
    {
        $year = str_pad($this->year()->asString(), 4, '0', STR_PAD_LEFT);
        $month = str_pad($this->month()->asString(), 2, '0', STR_PAD_LEFT);
        $lastDayOfMonth = $this->daysInMonth()->asString();
        return DateTimePlus::from($year . '-'. $month . '-' . $lastDayOfMonth . 'T00:00:00+00:00', FormatWithTimezone::ATOM);
    }

    public function daysInMonth() : DaysInMonth
    {
        return DaysInMonth::from(cal_days_in_month(CAL_GREGORIAN, $this->month()->asInt(), $this->year()->asInt()));
    }
}
