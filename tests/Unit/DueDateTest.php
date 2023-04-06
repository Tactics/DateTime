<?php

declare(strict_types=1);


namespace Tactics\DateTime\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tactics\DateTime\ClockAwareDateTime;
use Tactics\DateTime\DateTimePlus;
use Tactics\DateTime\DueDate;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDueDate;

final class DueDateTest extends TestCase
{
    /**
     * @test
     * @dataProvider dueDateProvider
     */
    public function due_date(DateTimeImmutable $now, DateTimePlus $date, callable $tests): void
    {
        $date = ClockAwareDateTime::from(
            dateTimePlus: $date,
            clock: new MockClock($now)
        );

        try {
            $dueDate = DueDate::from($date);
        } catch (InvalidDueDate $e) {
            $dueDate = $e;
        }
        $tests($dueDate);
    }

    public function dueDateProvider(): iterable
    {
        yield 'A valid datetime in the future will successfully create a due date' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('2023-04-25T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DueDate|InvalidDueDate $dueDate) {
                self::assertEquals('2023-04-25', $dueDate->toPhpDateTime()->format('Y-m-d'));
            },
        ];
        yield 'A due date can not be in the past' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('2022-11-21T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DueDate|InvalidDueDate $dueDate) {
                self::assertInstanceOf(InvalidDueDate::class, $dueDate);
            },
        ];
        yield 'A due date can be compared against any datetime for equality' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('2023-02-02T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DueDate|InvalidDueDate $dueDate) {
                self::assertTrue($dueDate->isSameDay(
                    dateTime: DateTimePlus::from('2023-02-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));
                self::assertFalse($dueDate->isSameDay(
                    dateTime: DateTimePlus::from('2023-01-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));
            }
        ];
        yield 'A due date can be evaluated against a certain datetime to see whether is falls before this datetime' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('2023-02-02T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DueDate|InvalidDueDate $dueDate) {
                self::assertFalse($dueDate->isBefore(
                    dateTime: DateTimePlus::from('2023-01-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));

                self::assertTrue($dueDate->isBefore(
                    dateTime: DateTimePlus::from('2023-03-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));
            },
        ];
        yield 'A due date can be evaluated against a certain datetime to see whether is falls after this datetime' => [
            'now' => DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2023-01-01T00:00:00+00:00'),
            'date' => DateTimePlus::from('2023-02-02T00:00:00+00:00', FormatWithTimezone::ATOM),
            'test' => function (DueDate|InvalidDueDate $dueDate) {
                self::assertTrue($dueDate->isAfter(
                    dateTime: DateTimePlus::from('2023-01-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));

                self::assertFalse($dueDate->isAfter(
                    dateTime: DateTimePlus::from('2023-03-02T00:00:00+00:00', FormatWithTimezone::ATOM)
                ));
            },
        ];
    }
}
