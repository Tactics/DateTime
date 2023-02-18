<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IntlCalendar;
use IntlDateFormatter;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Exception\InvalidDateTimePlus;
use Tactics\DateTime\Exception\InvalidDateTimePlusFormatting;
use Throwable;

/**
 * DateTimePlus.
 *
 * A stricter, more robust and immutable implementation of DateTime.
 * That requires Format and Timezone on creation of the date.
 * I will, by design not guess these thing like a normal DateTime does.
 * It will also be default enforce immutability.
 */
final class DateTimePlus implements DateTimePlusInterface, EvolvableDateTimeInterface
{
    private readonly Carbon $carbon;

    private function __construct(
        private readonly string $raw,
        private readonly FormatWithTimezone $format,
    ) {
        $dateTime = DateTimeImmutable::createFromFormat(
            $this->format->value,
            $this->raw,
        );

        // Make sure format and raw string combination is valid.
        if (!$dateTime instanceof DateTimeImmutable) {
            throw InvalidDateTimePlus::invalidDate();
        }

        // DateTime manipulates "sort of" valid date (ex. 32/01/2022)
        // We only allow a strictly valid DateTime. When DateTime manipulates
        // it's input 'getLastErrors' will contain info about the manipulation.
        // So we check to make sure there are no manipulations.
        $errors = $dateTime::getLastErrors();
        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw InvalidDateTimePlus::notStrictlyValid($errors);
        }

        // Internally we use Carbon for easy calculations.
        // but we don't expose this, so we can switch to any library we want.
        $this->carbon = new Carbon($dateTime, $dateTime->getTimezone());
    }

    /**
     * We only allow creation from a raw string in a timezone aware format
     * since php dateTime manipulates invalid dates and after that there is
     * no way of knowing whether the created DateTime was itself created from
     * an invalid string.
     */
    public static function from(
        string $raw,
        FormatWithTimezone $format,
    ): DateTimePlus {
        return new DateTimePlus(
            raw: $raw,
            format: $format,
        );
    }

    public function toTimeZone(DateTimeZone $timeZone): DateTimePlus
    {
        $carbon = clone $this->carbon;
        $carbon->setTimezone($timeZone);
        return new DateTimePlus(
            raw: $carbon->format(FormatWithTimezone::ATOM->value),
            format: FormatWithTimezone::ATOM,
        );
    }

    public function toPhpDateTime(): DateTimeImmutable
    {
        return $this->carbon->toDateTimeImmutable();
    }

    public function isSameDay(DateTimeInterface $targetObject): bool
    {
        return $this->carbon
            ->isSameDay($targetObject);
    }

    public function isBefore(DateTimeInterface $targetObject): bool
    {
        $toCarbon = (new Carbon($targetObject, $targetObject->getTimezone()))
            ->startOfDay();
        return $this->carbon->startOfDay()->isBefore($toCarbon);
    }

    public function isAfter(DateTimeInterface $targetObject): bool
    {
        $toCarbon = (new Carbon($targetObject, $targetObject->getTimezone()))
            ->startOfDay();
        return $this->carbon->startOfDay()->isAfter($toCarbon);
    }

    public function add($years = 0, $months = 0, $days = 0): DateTimePlus
    {
        $carbon = clone $this->carbon;
        $sum = $carbon->addYears($years)->addMonths($months)->addDays($days);
        return self::from(
            $sum->toDateTimeImmutable()->format(FormatWithTimezone::ATOM->value),
            FormatWithTimezone::ATOM,
        );
    }

    public function addTime($hours = 0, $minutes = 0, $seconds = 0): DateTimePlus
    {
        $carbon = clone $this->carbon;
        $sum = $carbon->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
        return self::from(
            $sum->toDateTimeImmutable()->format(FormatWithTimezone::ATOM->value),
            FormatWithTimezone::ATOM,
        );
    }

    /**
     * format.
     *
     * Local aware formatting works with different formats than php DateTime.
     * It works with unicode date symbols.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
     * @see https://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
     **/
    public function formatPlus(
        string $format,
        string $locale,
        ?DateTimeZone $displayTimeZone = null,
        ?IntlCalendar $calendar = null,
    ): string {
        $timezone = $displayTimeZone ?: $this->toPhpDateTime()->getTimezone();

        $formatter = new IntlDateFormatter(
            locale: $locale,
            dateType: IntlDateFormatter::FULL, // defaults that won't be use since we always provide a pattern
            timeType: IntlDateFormatter::FULL, // defaults that won't be use since we always provide a pattern
            timezone: $timezone,
            calendar: $calendar ?: IntlCalendar::createInstance($timezone, $locale),
            pattern: $format,
        );

        $formatted = $formatter->format($this->carbon->toDateTime());
        if (!$formatted) {
            throw InvalidDateTimePlusFormatting::failedFormatting($format, $locale, $timezone);
        }

        return $formatted;
    }

    public function getTimestamp(): int
    {
        return $this->carbon->getTimestamp();
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->carbon->getTimezone();
    }
}
