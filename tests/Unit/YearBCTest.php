<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\YearBC;
use Throwable;

final class YearBCTest extends TestCase
{
    /**
     * @test
     * @dataProvider yearProvider
     */
    public function year_bc(int $year, callable $tests): void
    {
        try {
            $yearBc = YearBC::for($year);
        } catch (Throwable $e) {
            $yearBc = $e;
        }
        $tests($yearBc);
    }

    public function yearProvider(): iterable
    {
        yield 'A positive int will successfully create a BC Year and turn in into a negative int' => [
            'year' => -12,
            'test' => function (YearBC|Throwable $year) {
                self::assertEquals('-12', $year->toInt());
            },
        ];
        yield 'A year can traverse to the next year' => [
            'year' => -12,
            'test' => function (YearBC|Throwable $year) {
                self::assertEquals('-11', $year->next()->toInt());
            },
        ];
        yield 'A year can traverse to the previous year' => [
            'year' => -12,
            'test' => function (YearBC|Throwable $year) {
                self::assertEquals('-13', $year->previous()->toInt());
            },
        ];
        yield 'A year can not traverse to a next year after zero' => [
            'year' => 0,
            'test' => function (YearBC|Throwable $year) {
                try {
                    $result = $year->next()->toInt();
                } catch (Throwable $e) {
                    $result = $e;
                }
                self::assertInstanceOf(InvalidArgumentException::class, $result);
            },
        ];
    }
}
