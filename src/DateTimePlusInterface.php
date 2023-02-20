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
    public function isSameDay(DateTimeInterface $targetObject): bool;

    public function isBefore(DateTimeInterface $targetObject): bool;

    public function isAfter(DateTimeInterface $targetObject): bool;

    public function formatPlus(string $format, string $locale, DateTimeZone $timeZone, ?IntlCalendar $calendar = null): string;
}
