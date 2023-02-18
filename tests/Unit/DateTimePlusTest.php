<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
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

        yield 'A date can be modified by years' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1996-04-25', $date->add(
                    years: 10
                )->toPhpDateTime()->format('Y-m-d'));
            },
        ];

        yield 'A date can be modified by months' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-08-25', $date->add(
                    months: 4
                )->toPhpDateTime()->format('Y-m-d'));
            },
        ];

        yield 'A date can be modified by days' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-27', $date->add(
                    days: 2
                )->toPhpDateTime()->format('Y-m-d'));
            },
        ];

        yield 'A date can be modified by years, months and days' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1987-06-27', $date->add(
                    years: 1,
                    months: 2,
                    days: 2
                )->toPhpDateTime()->format('Y-m-d'));
            },
        ];

        yield 'A date can be modified by hours' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-26T02:00:00+00:00', $date->addTime(
                    hours: 14
                )->toPhpDateTime()->format(DateTimeInterface::ATOM));
            },
        ];

        yield 'A date can be modified by minutes' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-25T13:02:00+00:00', $date->addTime(
                    minutes: 62
                )->toPhpDateTime()->format(DateTimeInterface::ATOM));
            },
        ];

        yield 'A date can be modified by seconds' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-25T12:01:02+00:00', $date->addTime(
                    seconds: 62
                )->toPhpDateTime()->format(DateTimeInterface::ATOM));
            },
        ];

        yield 'A date can be modified by hours, minutes and seconds' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertEquals('1986-04-25T13:02:02+00:00', $date->addTime(
                    hours: 1,
                    minutes: 2,
                    seconds: 2
                )->toPhpDateTime()->format(DateTimeInterface::ATOM));
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
    public function date_convert(
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
}
