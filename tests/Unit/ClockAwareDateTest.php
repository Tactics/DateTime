<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDateTime;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;

final class ClockAwareDateTest extends TestCase
{
    /**
     * @test
     * @dataProvider clockAwareDateProvider
     */
    public function clock_aware_date(DateTimeImmutable $now, DateTimePlus $dateTimePlus, callable $tests): void
    {
        $date = ClockAwareDateTime::from(
            dateTimePlus: $dateTimePlus,
            clock: new MockClock($now)
        );
        $tests($date);
    }

    public function clockAwareDateProvider(): iterable
    {
        yield 'A valid datetime will successfully create a clock aware date' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '1986-04-25T12:00:00+00:00'
            ),
            'dateTimePlus' => DateTimePlus::from(
                '1986-04-25T12:00:00+00:00',
                FormatWithTimezone::ATOM
            ),
            'test' => function (ClockAwareDateTime $clockAwareDate) {
                self::assertEquals('1986-04-25T12:00:00+00:00', $clockAwareDate->asDateTimePlus()->toPhpDateTime()->format(DateTimeInterface::ATOM));
            },
        ];
        yield 'A clock aware date can return the current datetime' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '2023-01-01T12:00:00+00:00'
            ),
            'dateTimePlus' => DateTimePlus::from(
                '1986-04-25T12:00:00+00:00',
                FormatWithTimezone::ATOM
            ),
            'test' => function (ClockAwareDateTime $clockAwareDate) {
                self::assertEquals('2023-01-01T12:00:00+00:00', $clockAwareDate->now()->format(DateTimeInterface::ATOM));
            },
        ];
        yield 'A clock aware date knows if it is in the future' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '2023-01-01T12:00:00+00:00'
            ),
            'dateTimePlus' => DateTimePlus::from(
                '2023-01-02T12:00:00+00:00',
                FormatWithTimezone::ATOM
            ),
            'test' => function (ClockAwareDateTime $clockAwareDate) {
                self::assertTrue($clockAwareDate->isFuture());
                self::assertFalse($clockAwareDate->isPast());
            },
        ];
        yield 'A clock aware date knows if it is in the past' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '2023-01-01T12:00:00+00:00'
            ),
            'dateTimePlus' => DateTimePlus::from(
                '2022-01-02T12:00:00+00:00',
                FormatWithTimezone::ATOM
            ),
            'test' => function (ClockAwareDateTime $clockAwareDate) {
                self::assertTrue($clockAwareDate->isPast());
                self::assertFalse($clockAwareDate->isFuture());
            },
        ];
    }
}
