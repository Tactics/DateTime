<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockPlus;

final class ClockPlusTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function clock(
        DateTimeImmutable $now,
        callable $tests
    ): void {
        $clock = ClockPlus::create(
            clock: new MockClock($now),
        );
        $tests($clock);
    }

    public function dataProvider(): iterable
    {
        yield 'A clock can tell the current datetime' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '1986-04-25T12:00:00+00:00'
            ),
            'test' => function (ClockPlus $date) {
                self::assertEquals(
                    '1986-04-25T12:00:00+00:00',
                    $date->now()->format(DateTimeInterface::ATOM)
                );
            },
        ];

        yield 'A clock can tell the current as DateTimePlus' => [
            'now' => DateTimeImmutable::createFromFormat(
                DateTimeInterface::ATOM,
                '1986-04-25T12:00:00+00:00'
            ),
            'test' => function (ClockPlus $date) {
                self::assertEquals(
                    '1986-04-25T12:00:00+00:00',
                    $date->nowPlus()->toPhpDateTime()->format(DateTimeInterface::ATOM)
                );
            },
        ];
    }
}
