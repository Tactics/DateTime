<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use IntlCalendar;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDateTimePlus;
use Tactics\DateTime\Exception\InvalidDateTimePlusFormatting;

final class DateTimePlusTest extends TestCase
{
    /**
     * @test
     * @dataProvider dateProvider
     */
    public function date(
        string $raw,
        FormatWithTimezone $format,
        callable $tests
    ): void {
        try {
            $date = DateTimePlus::from(
                raw: $raw,
                format: $format,
            );
        } catch (InvalidDateTimePlus $e) {
            $date = $e;
        }
        $tests($date);
    }

    public function dateProvider(): iterable
    {
        yield 'A valid datetime in ATOM format will successfully create a date' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-25', $date->formatPlus('yyyy-MM-dd', 'en'));
                self::assertEquals('1986-04-25', $date->formatPlus('yyyy-MM-dd', 'nl_be', new DateTimeZone('CET')));
                self::assertEquals('april 1986', $date->formatPlus('MMMM yyyy', 'nl_be', new DateTimeZone('CET')));
                self::assertEquals('avril 1986', $date->formatPlus('MMMM yyyy', 'fr_be', new DateTimeZone('CET')));
                self::assertEquals('1986-04-26T00:00:00', $date->formatPlus("yyyy-MM-dd'T'HH:mm:ss", 'nl_be', new DateTimeZone('Pacific/Wallis')));
                self::assertEquals('26 april', $date->formatPlus("dd MMMM", 'nl_be', new DateTimeZone('Pacific/Wallis')));
                self::assertEquals('514814400', $date->getTimestamp());
                self::assertEquals('+00:00', $date->getTimezone()->getName());
            },
        ];
        yield 'A date must be derived from a valid datetime and format combination' => [
            'raw' => '1986-04-32T12',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertInstanceOf(InvalidDateTimePlus::class, $date);
                self::assertEquals(InvalidDateTimePlus::INVALID_DATE, $date->getCode());
            },
        ];
        yield 'A date must be derived from a strictly correct datetime without warnings or errors before it successfully gets created' => [
            'raw' => '1986-04-32T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertInstanceOf(InvalidDateTimePlus::class, $date);
                self::assertEquals(InvalidDateTimePlus::NOT_STRICTLY_VALID_DATE, $date->getCode());
            },
        ];
        yield 'A date can be compared against any datetime for day equality' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertTrue($date->isSameDay(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T23:59:59+00:00')
                ));
                self::assertFalse($date->isSameDay(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T00:00:00+00:00')
                ));
                self::assertTrue($date->isSameDay(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T23:59:59+02:00')
                ));
            }
        ];
        yield 'A date can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertFalse($date->isBefore(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-24T00:00:00+00:00')
                ));

                self::assertTrue($date->isBefore(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T00:00:00+00:00')
                ));

                self::assertFalse($date->isBefore(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T11:00:00+02:00')
                ));

                self::assertTrue($date->isBefore(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T13:00:00-02:00')
                ));
            },
        ];

        yield 'A date can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertTrue($date->isAfter(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-24T12:00:00+00:00')
                ));

                self::assertFalse($date->isAfter(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T12:00:00+00:00')
                ));

                self::assertTrue($date->isAfter(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T11:00:00+02:00')
                ));

                self::assertFalse($date->isAfter(
                    targetObject: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T13:00:00-02:00')
                ));
            },
        ];
    }

    /**
     * @test
     * @dataProvider dateFormattingProvider
     */
    public function date_formatting(
        DateTimePlus $dateTimePlus,
        string $format,
        string $locale,
        DateTimeZone|null $displayTimeZone,
        IntlCalendar|null $calendar,
        callable $tests
    ): void {
        try {
            $date = $dateTimePlus->formatPlus($format, $locale);
        } catch (InvalidDateTimePlusFormatting $e) {
            $date = $e;
        }
        $tests($date);
    }

    public function dateFormattingProvider(): iterable
    {
        yield 'formatting a date time plus requires a valid format' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'format' => 'invalid',
            'locale' => 'en_US',
            'timeZone' => null,
            'calendar' => null,
            'test' => function (string|InvalidDateTimePlusFormatting $formatted) {
                self::assertInstanceOf(InvalidDateTimePlusFormatting::class, $formatted);
                self::assertEquals(InvalidDateTimePlusFormatting::FAILED_FORMATTING, $formatted->getCode());
            },
        ];
    }

    /**
     * @test
     * @dataProvider dateConvertProvider
     */
    public function date_convert_timezone(
        DateTimePlus $dateTimePlus,
        DateTimeZone $timeZone,
        callable $tests
    ): void {
        $date = $dateTimePlus->toTimeZone($timeZone);
        $tests($date);
    }

    public function dateConvertProvider(): iterable
    {
        yield 'a DateTimePlus can be converted to any timezone' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'timeZone' => new DateTimeZone('CET'),
            'test' => function (DateTimePlus $converted) {
                self::assertEquals('1986-04-25T13:00:00+01:00', $converted->formatPlus("yyyy-MM-dd'T'HH:mm:ssxxx", 'en'));
            },
        ];
    }

    /**
     * @test
     * @dataProvider dateAddTimeProvider
     */
    public function date_convert_add_time(
        DateTimePlus $dateTimePlus,
        int $hours,
        int $minutes,
        int $seconds,
        callable $tests
    ): void {
        $date = $dateTimePlus->addTime(
            hours: $hours,
            minutes: $minutes,
            seconds: $seconds
        );
        $tests($dateTimePlus, $date);
    }

    public function dateAddTimeProvider(): Generator
    {
        yield 'A date can be modified by hours' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'hours' => 14,
            'minutes' => 0,
            'seconds' => 0,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-04-26T02:00:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by minutes' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'hours' => 0,
            'minutes' => 62,
            'seconds' => 0,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-04-25T13:02:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by seconds' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 62,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-04-25T12:01:02+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by hours, minutes and seconds' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'hours' => 1,
            'minutes' => 2,
            'seconds' => 2,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-04-25T13:02:02+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];
    }

    /**
     * @test
     * @dataProvider dateAddProvider
     */
    public function date_convert_add(
        DateTimePlus $dateTimePlus,
        int $years,
        int $months,
        int $days,
        callable $tests
    ): void {
        $date = $dateTimePlus->add(
            years: $years,
            months: $months,
            days: $days
        );
        $tests($dateTimePlus, $date);
    }

    public function dateAddProvider(): Generator
    {
        yield 'A date can be modified by years' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'years' => 10,
            'months' => 0,
            'days' => 0,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1996-04-25T12:00:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by months' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'years' => 0,
            'months' => 4,
            'days' => 0,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-08-25T12:00:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by days' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'years' => 0,
            'months' => 0,
            'days' => 2,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1986-04-27T12:00:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];

        yield 'A date can be modified by years, months and days' => [
            'dateTimePlus' => DateTimePlus::from('1986-04-25T12:00:00+00:00', FormatWithTimezone::ATOM),
            'years' => 1,
            'months' => 2,
            'days' => 2,
            'test' => function (DateTimePlus $original, DateTimePlus $date) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $original->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
                self::assertEquals('1987-06-27T12:00:00+00:00', $date->toPhpDateTime()->format(FormatWithTimezone::ATOM->value));
            },
        ];
    }
}
