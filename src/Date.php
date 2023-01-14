<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class Date implements MutableDateInterface
{
    private readonly Carbon $carbon;

    private function __construct(
        protected readonly DateTimeInterface $dateTime
    ) {
        $errors = $this->dateTime::getLastErrors();

        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw new InvalidArgumentException(
                sprintf(
                    'A day of birth can only be created from a strictly valid DateTimeImmutable.
                    The passed DateTime contained %s warnings : %s and %s errors : %s ',
                    $errors['warning_count'],
                    implode(
                        separator: ',',
                        array: $errors['warnings']
                    ),
                    $errors['error_count'],
                    implode(
                        separator: ',',
                        array: $errors['errors']
                    ),
                )
            );
        }

        $dateTimeImmutable = DateTimeImmutable::createFromInterface($dateTime);
        $this->carbon = new Carbon($dateTimeImmutable, $dateTimeImmutable->getTimezone());
    }

    public static function from(DateTimeInterface $dateTime): Date
    {
        return new Date($dateTime);
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->carbon->toDateTimeImmutable();
    }

    public function isSame(DateTimeInterface $dateTime): bool
    {
        return $this->carbon
            ->isSameDay($dateTime);
    }

    public function isBefore(DateTimeInterface $dateTime): bool
    {
        $toCarbon = (new Carbon($dateTime, $dateTime->getTimezone()))
            ->startOfDay();
        return $this->carbon->startOfDay()->isBefore($toCarbon);
    }

    public function isAfter(DateTimeInterface $dateTime): bool
    {
        $toCarbon = (new Carbon($dateTime, $dateTime->getTimezone()))
            ->startOfDay();
        return $this->carbon->startOfDay()->isAfter($toCarbon);
    }

    public function add($years = 0, $months = 0, $days = 0): Date
    {
        $sum = $this->carbon->addYears($years)->addMonths($months)->addDays($days);
        return self::from($sum->toDateTimeImmutable());
    }
}
