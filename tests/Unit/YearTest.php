<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Year;
use Throwable;

final class YearTest extends TestCase
{
    /**
     * @test
     * @dataProvider yearProvider
     */
    public function year(int $year, callable $tests): void
    {
        try {
            $yearAd = Year::for($year);
        } catch (Throwable $e) {
            $yearAd = $e;
        }
        $tests($yearAd);
    }

    public function yearProvider(): iterable
    {
        yield 'A positive int of 4 digits will successfully create a Year' => [
            'year' => 2023,
            'test' => function (Year|Throwable $year) {
                self::assertEquals('2023', $year->toInt());
            },
        ];
        yield 'A year can traverse to the next year' => [
            'year' => 2023,
            'test' => function (Year|Throwable $year) {
                self::assertEquals('2024', $year->next()->toInt());
            },
        ];
        yield 'A year can traverse to the previous year' => [
            'year' => 2023,
            'test' => function (Year|Throwable $year) {
                self::assertEquals('2022', $year->previous()->toInt());
            },
        ];
    }
}
