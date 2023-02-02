<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDate;
use Tactics\DateTime\DayOfBirth;
use Tactics\DateTime\DueDate;
use Tactics\DateTime\YearsOfAge;
use Throwable;

final class YearsOfAgeTest extends TestCase
{
    /**
     * @test
     * @dataProvider fromProvider
     */
    public function years_of_age_from(int $years, int $months, callable $tests): void
    {
        try {
            $yearsOfAge = YearsOfAge::from($years, $months);
        } catch (InvalidArgumentException $e) {
            $yearsOfAge = $e;
        }
        $tests($yearsOfAge);
    }

    /**
     * @test
     * @dataProvider onProvider
     */
    public function years_of_age_on(string $now, string $day_of_birth, callable $tests): void
    {
        $now = DateTimeImmutable::createFromFormat('Y-m-d', $now);
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $day_of_birth);
        $date = ClockAwareDate::from(
            dateTime: $dateTime,
        );
        $dayOfBirth = DayOfBirth::from($date);
        try {
            $yearsOfAge = YearsOfAge::on($now, $dayOfBirth);
        } catch (InvalidArgumentException $e) {
            $yearsOfAge = $e;
        }
        $tests($yearsOfAge);
    }

    public function onProvider(): iterable
    {
        yield 'The age in years on a certain day can be derived' => [
            'now' => '2023-01-01',
            'day_of_birth' => '2020-04-25',
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
            },
        ];

        yield 'The age in months on a certain day can be derived' => [
            'now' => '2023-01-01',
            'day_of_birth' => '2020-04-25',
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('32', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age is always 0 when the day of birth is before the compared date' => [
            'now' => '2020-04-25',
            'day_of_birth' => '2023-01-01',
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('0', $yearsOfAge->inYears());
                self::assertEquals('0', $yearsOfAge->inMonths());
            },
        ];
    }

    public function fromProvider(): iterable
    {
        yield 'The age in years can be created from months only' => [
            'years' => 0,
            'months' => 34,
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
                self::assertEquals('34', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age in years can be created from years only' => [
            'years' => 4,
            'months' => 0,
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('4', $yearsOfAge->inYears());
                self::assertEquals('48', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age in years can be created from months and years only' => [
            'years' => 2,
            'months' => 8,
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
                self::assertEquals('32', $yearsOfAge->inMonths());
            },
        ];

        yield 'A year of age can only be a positive number' => [
            'years' => -2,
            'months' => 0,
            'test' => function (YearsOfAge|InvalidArgumentException $yearsOfAge) {
                self::assertInstanceOf(InvalidArgumentException::class, $yearsOfAge);
            },
        ];


    }

}
