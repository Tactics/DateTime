<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Exception\InvalidMonth;
use Tactics\DateTime\Month;
use Tactics\DateTime\Year;
use Tactics\DateTime\YearAndMonth;

final class YearAndMonthTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function year_and_month(
        Year  $year,
        Month $month,
        callable $tests
    ): void
    {
        $month = YearAndMonth::from($year, $month);
        $tests($month);
    }

    public function dataProvider(): iterable
    {
        yield 'A year and month can be created' => [
            'year' => Year::from(2024),
            'month' => Month::from(4),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals(2024, $yearAndMonth->year()->asInt());
                self::assertEquals(4, $yearAndMonth->month()->asInt());
            },
        ];

        yield 'A year and month can give us the number of days in a month (leap year)' => [
            'year' => Year::from(2024),
            'month' => Month::from(2),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals(29, $yearAndMonth->daysInMonth()->asInt());
            },
        ];

        yield 'A year and month can give us the number of days in a month in the future' => [
            'year' => Year::from(2026),
            'month' => Month::from(2),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals(28, $yearAndMonth->daysInMonth()->asInt());
            },
        ];

        yield 'A year and month can give us the number of days in a month in the past' => [
            'year' => Year::from(1986),
            'month' => Month::from(4),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals(30, $yearAndMonth->daysInMonth()->asInt());
            },
        ];

        yield 'A year and month can give us the first day of the month' => [
            'year' => Year::from(1986),
            'month' => Month::from(4),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals('1986-04-01', $yearAndMonth->firstDayOfMonth()->formatPlus('yyyy-MM-dd', 'en', new DateTimeZone('UTC')));
            },
        ];

        yield 'A year and month can give us the last day of the month' => [
            'year' => Year::from(1986),
            'month' => Month::from(4),
            'test' => function (YearAndMonth $yearAndMonth) {
                self::assertEquals('1986-04-30', $yearAndMonth->lastDayOfMonth()->formatPlus('yyyy-MM-dd', 'en', new DateTimeZone('UTC')));
            },
        ];
    }
}
