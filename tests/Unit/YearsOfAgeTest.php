<?php

declare(strict_types=1);


namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\ClockAwareDateTime;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\DayOfBirth;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDueDate;
use Tactics\DateTime\Exception\InvalidYearsOfAge;
use Tactics\DateTime\YearsOfAge;

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
        } catch (InvalidYearsOfAge $e) {
            $yearsOfAge = $e;
        }
        $tests($yearsOfAge);
    }

    /**
     * @test
     * @dataProvider onProvider
     */
    public function years_of_age_on(DateTimePlus $now, DateTimePlus $day_of_birth, callable $tests): void
    {
        $date = ClockAwareDateTime::from(
            dateTimePlus: $day_of_birth,
        );
        $dayOfBirth = DayOfBirth::from($date);
        try {
            $yearsOfAge = YearsOfAge::on($now, $dayOfBirth);
        } catch (InvalidYearsOfAge $e) {
            $yearsOfAge = $e;
        }
        $tests($yearsOfAge);
    }

    public function onProvider(): iterable
    {
        yield 'The age in years on a certain day can be derived' => [
            'now' => DateTimePlus::from('2023-01-01T12:00:00+00:00', FormatWithTimezone::ATOM),
            'day_of_birth' => DateTimePlus::from('2020-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
            },
        ];

        yield 'The age in months on a certain day can be derived' => [
            'now' => DateTimePlus::from('2023-01-01T12:00:00+00:00', FormatWithTimezone::ATOM),
            'day_of_birth' => DateTimePlus::from('2020-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertEquals('32', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age is always 0 when the day of birth is before the compared date' => [
            'now' => DateTimePlus::from('2020-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'day_of_birth' => DateTimePlus::from('2023-01-01T12:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
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
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
                self::assertEquals('34', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age in years can be created from years only' => [
            'years' => 4,
            'months' => 0,
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertEquals('4', $yearsOfAge->inYears());
                self::assertEquals('48', $yearsOfAge->inMonths());
            },
        ];

        yield 'The age in years can be created from months and years only' => [
            'years' => 2,
            'months' => 8,
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertEquals('2', $yearsOfAge->inYears());
                self::assertEquals('32', $yearsOfAge->inMonths());
            },
        ];

        yield 'A year of age can only be a positive number' => [
            'years' => -2,
            'months' => 0,
            'test' => function (YearsOfAge|InvalidYearsOfAge $yearsOfAge) {
                self::assertInstanceOf(InvalidYearsOfAge::class, $yearsOfAge);
            },
        ];
    }
}
