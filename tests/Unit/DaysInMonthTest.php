<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Day;
use Tactics\DateTime\DaysInMonth;
use Tactics\DateTime\Exception\InvalidDaysInMonth;

final class DaysInMonthTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function days_in_month(
        int      $value,
        callable $tests
    ): void
    {
        try {
            $days = DaysInMonth::from($value);
        } catch (InvalidDaysInMonth $e) {
            $days = $e;
        }
        $tests($days);
    }


    public function dataProvider(): iterable
    {
        yield 'Days in a month can not be less than 28' => [
            'value' => 0,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertInstanceOf(InvalidDaysInMonth::class, $days);
            },
        ];

        yield 'Days in month can not be more than 31' => [
            'value' => 32,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertInstanceOf(InvalidDaysInMonth::class, $days);
            },
        ];

        yield 'Days in month can be created from a number between 28 and 31' => [
            'value' => 29,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertEquals(29, $days->asInt());
            },
        ];

        yield 'Days in month can be created from 28' => [
            'value' => 28,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertEquals(28, $days->asInt());
            },
        ];

        yield 'Days in month can be created from 31' => [
            'value' => 31,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertEquals(31, $days->asInt());
            },
        ];

        yield 'Days in month can outputted as a string' => [
            'value' => 31,
            'test' => function (DaysInMonth|InvalidDaysInMonth $days) {
                self::assertEquals('31', $days->asString());
            },
        ];

    }
}
