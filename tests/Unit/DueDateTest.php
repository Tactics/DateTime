<?php

declare(strict_types=1);

namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDate;
use Tactics\DateTime\DueDate;
use Throwable;

final class DueDateTest extends TestCase
{
    /**
     * @test
     * @dataProvider dueDateProvider
     */
    public function due_date(string $now, string $date, callable $tests): void
    {
        $now = DateTimeImmutable::createFromFormat('Y-m-d', $now);
        $date = ClockAwareDate::from(
            dateTime: DateTimeImmutable::createFromFormat('Y-m-d', $date),
            clock: new MockClock($now)
        );

        try {
            $dueDate = DueDate::from($date);
        } catch (InvalidArgumentException $e) {
            $dueDate = $e;
        }
        $tests($dueDate);
    }

    public function dueDateProvider(): iterable
    {
        yield 'A valid datetime in the future will successfully create a due date' => [
            'now' => '2023-01-01',
            'date' => '2023-04-25',
            'test' => function (DueDate|InvalidArgumentException $dueDate) {
                self::assertEquals('2023-04-25', $dueDate->toDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A due date can not be in the past' => [
            'now' => '2023-01-01',
            'date' => '2022-11-21',
            'test' => function (DueDate|InvalidArgumentException $dueDate) {
                self::assertInstanceOf(InvalidArgumentException::class, $dueDate);
            },
        ];
        yield 'A due date can be compared against any datetime for equality' => [
            'now' => '2023-01-01',
            'date' => '2023-02-02',
            'test' => function (DueDate|InvalidArgumentException $dueDate) {
                self::assertTrue($dueDate->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-02-02')
                ));
                self::assertFalse($dueDate->isSame(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-02')
                ));
            }
        ];
        yield 'A due date can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'now' => '2023-01-01',
            'date' => '2023-02-02',
            'test' => function (DueDate|InvalidArgumentException $dueDate) {
                self::assertFalse($dueDate->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-02')
                ));

                self::assertTrue($dueDate->isBefore(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-03-02')
                ));
            },
        ];
        yield 'A due date can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'now' => '2023-01-01',
            'date' => '2023-02-02',
            'test' => function (DueDate|InvalidArgumentException $dueDate) {
                self::assertTrue($dueDate->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-02')
                ));

                self::assertFalse($dueDate->isAfter(
                    dateTime: DateTimeImmutable::createFromFormat('Y-m-d', '2023-03-02')
                ));
            },
        ];
    }
}
