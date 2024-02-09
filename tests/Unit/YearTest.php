<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Exception\InvalidYear;
use Tactics\DateTime\Year;

final class YearTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function year(
        int $value,
        callable $tests
    ): void
    {
        try {
            $year = Year::from($value);
        } catch (InvalidYear $e) {
            $year = $e;
        }
        $tests($year);
    }

    public function dataProvider(): iterable
    {
        yield 'A year can only be a positive number' => [
            'value' => -100,
            'test' => function (Year|InvalidYear $year) {
                self::assertInstanceOf(InvalidYear::class, $year);
            },
        ];

        yield 'A year can be created from a positive number' => [
            'value' => 2024,
            'test' => function (Year|InvalidYear $year) {
                self::assertEquals(2024, $year->asInt());
            },
        ];

        yield 'A year can only be any positive number' => [
            'value' => 10000,
            'test' => function (Year|InvalidYear $year) {
                self::assertEquals(10000, $year->asInt());
            },
        ];

        yield 'A year can give us the first day of the year' => [
            'value' => 2024,
            'test' => function (Year|InvalidYear $year) {
                self::assertEquals(1, $year->firstDay()->day()->asInt());
                self::assertEquals(1, $year->firstDay()->month()->asInt());
                self::assertEquals(2024, $year->firstDay()->year()->asInt());
            },
        ];

        yield 'A year can give us the last day of the year' => [
            'value' => 2024,
            'test' => function (Year|InvalidYear $year) {
                self::assertEquals(31, $year->lastDay()->day()->asInt());
                self::assertEquals(12, $year->lastDay()->month()->asInt());
                self::assertEquals(2024, $year->lastDay()->year()->asInt());
            },
        ];

        yield 'A year can be create from 0' => [
            'value' => 0,
            'test' => function (Year|InvalidYear $year) {
                self::assertEquals(31, $year->lastDay()->day()->asInt());
                self::assertEquals(12, $year->lastDay()->month()->asInt());
                self::assertEquals(0, $year->lastDay()->year()->asInt());
            },
        ];
    }

}
