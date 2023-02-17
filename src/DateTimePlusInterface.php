<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IntlCalendar;

interface DateTimePlusInterface
{
    public function toPhpDateTime(): DateTimeImmutable;

    public function isSameDay(DateTimeInterface $dateTime): bool;

    public function isBefore(DateTimeInterface $dateTime): bool;

    public function isAfter(DateTimeInterface $dateTime): bool;

    public function format(string $format, string $locale, ?DateTimeZone $displayTimeZone = null, ?IntlCalendar $calendar = null): string;
}
