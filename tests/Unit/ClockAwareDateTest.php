<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDate;
use Throwable;

final class ClockAwareDateTest extends TestCase
{
    /**
     * @test
     * @dataProvider clockAwareDateProvider
     */
    public function clock_aware_date(string $now, string $date, callable $tests): void
    {
        $now = DateTimeImmutable::createFromFormat('Y-m-d', $now);
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        try {
            $date = ClockAwareDate::from(
                dateTime: $dateTime,
                clock: new MockClock($now)
            );
        } catch (InvalidArgumentException $e) {
            $date = $e;
        }
        $tests($date);
    }

    public function clockAwareDateProvider(): iterable
    {
        yield 'A valid datetime will successfully create a clock aware date' => [
            'now' => '1986-04-25',
            'date' => '1986-04-25',
            'test' => function (ClockAwareDate|InvalidArgumentException $clockAwareDate) {
                self::assertEquals('1986-04-25', $clockAwareDate->date()->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A clock aware date can return the current datetime' => [
            'now' => '2023-01-01',
            'date' => '1986-04-25',
            'test' => function (ClockAwareDate|InvalidArgumentException $clockAwareDate) {
                self::assertEquals('2023-01-01', $clockAwareDate->now()->format('Y-m-d'));
            },
        ];
        yield 'A clock aware date knows if it is in the future' => [
            'now' => '2023-01-01',
            'date' => '2023-01-02',
            'test' => function (ClockAwareDate|InvalidArgumentException $clockAwareDate) {
                self::assertTrue($clockAwareDate->isFuture());
                self::assertFalse($clockAwareDate->isPast());
            },
        ];
        yield 'A clock aware date knows if it is in the past' => [
            'now' => '2023-01-01',
            'date' => '2022-01-02',
            'test' => function (ClockAwareDate|InvalidArgumentException $clockAwareDate) {
                self::assertTrue($clockAwareDate->isPast());
                self::assertFalse($clockAwareDate->isFuture());
            },
        ];
        yield 'A clock aware date must be derived from a strictly correct datetime without warnings or errors before it successfully gets created' => [
            'now' => '2023-01-01',
            'date' => '1986-04-32',
            'test' => function (ClockAwareDate|InvalidArgumentException $clockAwareDate) {
                self::assertInstanceOf(InvalidArgumentException::class, $clockAwareDate);
            },
        ];
    }
}
