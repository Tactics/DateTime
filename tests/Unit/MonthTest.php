<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Exception\InvalidMonth;
use Tactics\DateTime\Exception\InvalidYear;
use Tactics\DateTime\Month;
use Tactics\DateTime\Year;

final class MonthTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function month(
        int $value,
        callable $tests
    ): void
    {
        try {
            $month = Month::from($value);
        } catch (InvalidMonth $e) {
            $month = $e;
        }
        $tests($month);
    }

    public function dataProvider(): iterable
    {
        yield 'A month can not be less than 1' => [
            'value' => 0,
            'test' => function (Month|InvalidMonth $month) {
                self::assertInstanceOf(InvalidMonth::class, $month);
            },
        ];

        yield 'A month can not be more than 12' => [
            'value' => 2024,
            'test' => function (Month|InvalidMonth $month) {
                self::assertInstanceOf(InvalidMonth::class, $month);
            },
        ];

        yield 'A month can be created from a number between 1 and 12' => [
            'value' => 4,
            'test' => function (Month|InvalidMonth $month) {
                self::assertEquals(4, $month->asInt());
            },
        ];

        yield 'A month can be created from 1' => [
            'value' => 1,
            'test' => function (Month|InvalidMonth $month) {
                self::assertEquals(1, $month->asInt());
            },
        ];

        yield 'A month can be created from 12' => [
            'value' => 12,
            'test' => function (Month|InvalidMonth $month) {
                self::assertEquals(12, $month->asInt());
            },
        ];

        yield 'A month can be outputted as string' => [
            'value' => 12,
            'test' => function (Month|InvalidMonth $month) {
                self::assertEquals('12', $month->asString());
            },
        ];

    }

}

