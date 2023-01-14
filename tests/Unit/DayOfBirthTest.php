<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDate;
use Tactics\DateTime\DayOfBirth;
use Tactics\DateTime\YearsOfAge;
use Throwable;

final class DayOfBirthTest extends TestCase
{
    /**
     * @test
     * @dataProvider dayOfBirthProvider
     */
    public function day_of_birth(string $now, string $date, callable $tests): void
    {
        $now = DateTimeImmutable::createFromFormat('Y-m-d', $now);
        $date = ClockAwareDate::from(
            dateTime: DateTimeImmutable::createFromFormat('Y-m-d', $date),
            clock: new MockClock($now)
        );

        try {
            $dayOfBirth = DayOfBirth::from($date);
        } catch (Throwable $e) {
            $dayOfBirth = $e;
        }
        $tests($dayOfBirth);
    }

    public function dayOfBirthProvider(): iterable
    {
        yield 'A valid datetime in the past will successfully create a day of birth' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                self::assertEquals('1986-04-25', $dayOfBirth->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A birthday can be derived from the day of birth' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                $age = YearsOfAge::from(years: 16);
                $sweetSixteen = $dayOfBirth->when(age: $age);
                self::assertEquals('2002-04-25', $sweetSixteen->format('Y-m-d'));
            },
        ];
        yield 'A moment in time can be determined based on a date of birth' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                $age = YearsOfAge::from(years: 1, andXMonths: 10);
                $twentyTwoMonths = $dayOfBirth->when(age: $age);
                self::assertEquals('1988-02-25', $twentyTwoMonths->format('Y-m-d'));
            },
        ];
        yield 'A datetime can be compared against a birthday to validate a person is below a certain age' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertFalse($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-24')
                ));
            }
        ];
        yield 'A datetime can be compared against a birthday to validate a person is above a certain age' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertTrue($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-26')
                ));
            }
        ];
        yield 'A datetime can be compared against a birthday to validate a person turns a certain age on that day' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertTrue($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-25')
                ));
            }
        ];
        yield 'A day of birth can be compared against any datetime for equality' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                self::assertTrue($dayOfBirth->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-25')
                ));
                self::assertFalse($dayOfBirth->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1987-04-25')
                ));
            }
        ];
        yield 'A day of birth can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                self::assertFalse($dayOfBirth->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertTrue($dayOfBirth->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A day of birth can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                self::assertTrue($dayOfBirth->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertFalse($dayOfBirth->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A day of birth can not be in the future' => [
            'now' => '1986-01-01',
            'date' => '1986-04-25',
            'test' => function (DayOfBirth|Throwable $dayOfBirth) {
                self::assertInstanceOf(InvalidArgumentException::class, $dayOfBirth);
            },
        ];
    }
}
