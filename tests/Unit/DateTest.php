<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tactics\DateTime\Date;
use Throwable;

final class DateTest extends TestCase
{
    /**
     * @test
     * @dataProvider dateProvider
     */
    public function date(string $date, callable $tests): void
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        try {
            $date = Date::from(
                dateTime: $dateTime,
            );
        } catch (InvalidArgumentException $e) {
            $date = $e;
        }
        $tests($date);
    }

    public function dateProvider(): iterable
    {
        yield 'A valid datetime will successfully create a date' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertEquals('1986-04-25', $date->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A date must be derived from a strictly correct datetime without warnings or errors before it successfully gets created' => [
            'date' => '1986-04-32',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertInstanceOf(InvalidArgumentException::class, $date);
            },
        ];
        yield 'A date can be compared against any datetime for equality' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertTrue($date->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-25')
                ));
                self::assertFalse($date->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1987-04-25')
                ));
            }
        ];
        yield 'A date can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertFalse($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertTrue($date->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A date can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertTrue($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-24')
                ));

                self::assertFalse($date->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '1986-04-26')
                ));
            },
        ];
        yield 'A date can be modified by years' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertEquals('1996-04-25', $date->add(
                    years: 10
                )->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A date can be modified by months' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertEquals('1986-08-25', $date->add(
                    months: 4
                )->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A date can be modified by days' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertEquals('1986-04-27', $date->add(
                    days: 2
                )->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A date can be modified by years, months and days' => [
            'date' => '1986-04-25',
            'test' => function (Date|InvalidArgumentException $date) {
                self::assertEquals('1987-06-27', $date->add(
                    years: 1,
                    months: 2,
                    days: 2
                )->toDateTime()->format('Y-m-d'));
            },
        ];
    }
}
