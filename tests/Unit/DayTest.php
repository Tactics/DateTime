<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Day;
use Tactics\DateTime\Exception\InvalidDay;
use Tactics\DateTime\Exception\InvalidMonth;

final class DayTest extends TestCase
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
            $day = Day::from($value);
        } catch (InvalidDay $e) {
            $day = $e;
        }
        $tests($day);
    }

    public function dataProvider(): iterable
    {
        yield 'A day can not be less than 1' => [
            'value' => 0,
            'test' => function (Day|InvalidDay $day) {
                self::assertInstanceOf(InvalidDay::class, $day);
            },
        ];

        yield 'A day can not be more than 31' => [
            'value' => 32,
            'test' => function (Day|InvalidDay $day) {
                self::assertInstanceOf(InvalidDay::class, $day);
            },
        ];

        yield 'A month can be created from a number between 1 and 31' => [
            'value' => 4,
            'test' => function (Day|InvalidDay $day) {
                self::assertEquals(4, $day->asInt());
            },
        ];

        yield 'A month can be created from 1' => [
            'value' => 1,
            'test' => function (Day|InvalidDay $day) {
                self::assertEquals(1, $day->asInt());
            },
        ];

        yield 'A month can be created from 31' => [
            'value' => 31,
            'test' => function (Day|InvalidDay $day) {
                self::assertEquals(31, $day->asInt());
            },
        ];

        yield 'A day can outputted as string' => [
            'value' => 31,
            'test' => function (Day|InvalidDay $day) {
                self::assertEquals('31', $day->asString());
            },
        ];
    }

}

