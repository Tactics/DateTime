<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IntlCalendar;

/**
 * Note : DateTimeInterface can't be implemented by user classes
 * So we aim to provide the same functionalities in this interface
 * and extend them with Plus variants.
 */
interface DateTimePlusInterface
{
    public function toPhpDateTime(): DateTimeImmutable;

    public function isSameDay(DateTimeInterface $targetObject): bool;

    public function isBefore(DateTimeInterface $targetObject): bool;

    public function isAfter(DateTimeInterface $targetObject): bool;

    public function formatPlus(string $format, string $locale, ?DateTimeZone $displayTimeZone = null, ?IntlCalendar $calendar = null): string;

    public function getTimestamp(): int;

    public function getTimezone(): DateTimeZone;
}
