<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDateTimePlus;

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
    ): void
    {
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
                self::assertEquals('1986-04-25', $date->format('yyyy-MM-dd', 'en'));
                self::assertEquals('1986-04-25', $date->format('yyyy-MM-dd', 'nl_be', new DateTimeZone('Europe/Brussels')));
                self::assertEquals('april 1986', $date->format('MMMM yyyy', 'nl_be', new DateTimeZone('Europe/Brussels')));
                self::assertEquals('avril 1986', $date->format('MMMM yyyy', 'fr_be', new DateTimeZone('Europe/Brussels')));
                self::assertEquals('1986-04-26T00:00:00', $date->format("yyyy-MM-dd'T'HH:mm:ss", 'nl_be', new DateTimeZone('Pacific/Wallis')));
                self::assertEquals('26 april', $date->format("dd MMMM", 'nl_be', new DateTimeZone('Pacific/Wallis')));
            },
        ];
        yield 'A date must be derived from a strictly correct datetime without warnings or errors before it successfully gets created' => [
            'raw' => '1986-04-32T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertInstanceOf(InvalidDateTimePlus::class, $date);
            },
        ];
        yield 'A date can be compared against any datetime for day equality' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertTrue($date->isSameDay(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T23:59:59+00:00')
                ));
                self::assertFalse($date->isSameDay(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T00:00:00+00:00')
                ));
                self::assertTrue($date->isSameDay(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T23:59:59+02:00')
                ));
            }
        ];
        yield 'A date can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertFalse($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-24T00:00:00+00:00')
                ));

                self::assertTrue($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T00:00:00+00:00')
                ));

                self::assertFalse($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T11:00:00+02:00')
                ));

                self::assertTrue($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T13:00:00-02:00')
                ));
            },
        ];

        yield 'A date can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'raw' => '1986-04-25T12:00:00+00:00',
            'format' => FormatWithTimezone::ATOM,
            'test' => function (DateTimePlus|InvalidDateTimePlus $date) {
                self::assertTrue($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-24T12:00:00+00:00')
                ));

                self::assertFalse($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-26T12:00:00+00:00')
                ));

                self::assertTrue($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T11:00:00+02:00')
                ));

                self::assertFalse($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '1986-04-25T13:00:00-02:00')
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
    }
}