<?php

declare(strict_types=1);


namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDateTime;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\DayOfBirth;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDayOfBirth;
use Tactics\DateTime\YearsOfAge;
use Throwable;

final class DayOfBirthTest extends TestCase
{
    /**
     * @test
     * @dataProvider dayOfBirthProvider
     */
    public function day_of_birth(DateTimeImmutable $now, DateTimePlus $date, callable $tests): void
    {
        $date = ClockAwareDateTime::from(
            dateTimePlus: $date,
            clock: new MockClock($now)
        );

        try {
            $dayOfBirth = DayOfBirth::from($date);
        } catch (InvalidDayOfBirth $e) {
            $dayOfBirth = $e;
        }
        $tests($dayOfBirth);
    }

    public function dayOfBirthProvider(): iterable
    {
        yield 'A valid datetime in the past will successfully create a day of birth' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                self::assertEquals('1986-04-25T00:00:00+00:00', $dayOfBirth->toDateTime()->format(DateTimeInterface::ATOM));
            },
        ];
        yield 'A birthday can be derived from the day of birth' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                $age = YearsOfAge::from(years: 16);
                $sweetSixteen = $dayOfBirth->when(age: $age);
                self::assertEquals('2002-04-25', $sweetSixteen->format('Y-m-d'));
            },
        ];
        yield 'A moment in time can be determined based on a date of birth' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                $age = YearsOfAge::from(years: 1, andXMonths: 10);
                $twentyTwoMonths = $dayOfBirth->when(age: $age);
                self::assertEquals('1988-02-25', $twentyTwoMonths->format('Y-m-d'));
            },
        ];
        yield 'A datetime can be compared against a birthday to validate a person is below a certain age' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertFalse($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-24')
                ));
            }
        ];
        yield 'A datetime can be compared against a birthday to validate a person is above a certain age' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertTrue($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-26')
                ));
            }
        ];
        yield 'A datetime can be compared against a birthday to validate a person turns a certain age on that day' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                $eighteen = YearsOfAge::from(years: 18);
                self::assertTrue($dayOfBirth->is(
                    age: $eighteen,
                    on: DateTimeImmutable::createFromFormat('Y-m-d', '2004-04-25')
                ));
            }
        ];
        yield 'A day of birth can be compared against any datetime for day equality' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                self::assertTrue($dayOfBirth->isSameDay(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-25')
                ));
                self::assertFalse($dayOfBirth->isSameDay(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1987-04-25')
                ));
            }
        ];
        yield 'A day of birth can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                self::assertFalse($dayOfBirth->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertTrue($dayOfBirth->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A day of birth can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                self::assertTrue($dayOfBirth->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertFalse($dayOfBirth->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A day of birth can not be in the future' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('1986-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DayOfBirth|InvalidDayOfBirth $dayOfBirth) {
                self::assertInstanceOf(InvalidDayOfBirth::class, $dayOfBirth);
            },
        ];
    }
}
