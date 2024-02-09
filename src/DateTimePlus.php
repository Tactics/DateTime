<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use IntlCalendar;
use IntlDateFormatter;
use Psr\Clock\ClockInterface;
use Tactics\DateTime\Enum\DateTimePlus\FormatWithTimezone;
use Tactics\DateTime\Enum\DateTimePlus\StorageFormat;
use Tactics\DateTime\Exception\InvalidDateTimePlus;
use Tactics\DateTime\Exception\InvalidDateTimePlusFormatting;

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
            $this->format->pattern(),
            $this->raw,
        );

        // Make sure format and raw string combination is valid.
        if (!$dateTime instanceof DateTimeImmutable) {
            throw InvalidDateTimePlus::invalidDate($raw);
        }

        // DateTime manipulates "sort of" valid date (ex. 32/01/2022)
        // We only allow a strictly valid DateTime. When DateTime manipulates
        // it's input 'getLastErrors' will contain info about the manipulation.
        // So we check to make sure there are no manipulations.
        $errors = $dateTime::getLastErrors();
        if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw InvalidDateTimePlus::notStrictlyValid($errors, $raw);
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

    /**
     * We only allow creation from a UTC timestamp since it has timezone UTC implicitly.
     */
    public static function fromTimestampUTC(
        int $timestamp,
    ): DateTimePlus {
        $carbon = Carbon::createFromTimestampUTC($timestamp);
        return new DateTimePlus(
            raw: $carbon->format(FormatWithTimezone::ATOM->pattern()),
            format: FormatWithTimezone::ATOM,
        );
    }

    /**
     * We only allow creation from now if a clock is provided.
     * In theorie the clock could have a wrong timezone but that is
     * up to the implementation to make sure it is correct.
     */
    public static function fromNow(
        ClockInterface $clock
    ): DateTimePlus {
        return self::from(
            raw: $clock->now()->format(FormatWithTimezone::ATOM->pattern()),
            format: FormatWithTimezone::ATOM
        );
    }

    /**
     * Normal and best practice is to store a datetime in UTC, so
     * when no specific timezone is provided we convert the date to UTC
     * and return it in the storage format.
     */
    public function toStorage(
        StorageFormat $storageFormat,
        ?DateTimeZone $timeZone = null
    ): string {
        $timeZone = $timeZone ?? new DateTimeZone('UTC');
        $carbon = clone $this->carbon;
        $carbon->setTimezone($timeZone);
        return $carbon->format($storageFormat->pattern());
    }

    public function toTimezone(DateTimeZone $timeZone): DateTimePlus
    {
        $carbon = clone $this->carbon;
        $carbon->setTimezone($timeZone);
        return new DateTimePlus(
            raw: $carbon->format(FormatWithTimezone::ATOM->pattern()),
            format: FormatWithTimezone::ATOM,
        );
    }

    public function toPhpDateTime(): DateTimeImmutable
    {
        return $this->carbon->toDateTimeImmutable();
    }

    public function isSameDay(DateTimePlus $targetObject): bool
    {
        return $this->carbon
            ->isSameDay($targetObject->toPhpDateTime());
    }

    public function isBefore(DateTimePlus $targetObject): bool
    {
        $toCarbon = (new Carbon($targetObject->toPhpDateTime(), $targetObject->timezone()));
        return $this->carbon->isBefore($toCarbon);
    }

    public function isAfter(DateTimePlus $targetObject): bool
    {
        $toCarbon = (new Carbon($targetObject->toPhpDateTime(), $targetObject->timezone()));
        return $this->carbon->isAfter($toCarbon);
    }

    public function add(
        $years = 0,
        $months = 0,
        $days = 0,
        $hours = 0,
        $minutes = 0,
        $seconds = 0
    ): DateTimePlus {
        $carbon = clone $this->carbon;
        $sum = $carbon
            ->addYears($years)
            ->addMonths($months)
            ->addDays($days)
            ->addHours($hours)
            ->addMinutes($minutes)
            ->addSeconds($seconds);
        return self::from(
            $sum->toDateTimeImmutable()->format(FormatWithTimezone::ATOM->pattern()),
            FormatWithTimezone::ATOM,
        );
    }

    public function subtract(
        $years = 0,
        $months = 0,
        $days = 0,
        $hours = 0,
        $minutes = 0,
        $seconds = 0
    ): DateTimePlus {
        $carbon = clone $this->carbon;
        $sum = $carbon
            ->subYears($years)
            ->subMonths($months)
            ->subDays($days)
            ->subHours($hours)
            ->subMinutes($minutes)
            ->subSeconds($seconds);
        return self::from(
            $sum->toDateTimeImmutable()->format(FormatWithTimezone::ATOM->pattern()),
            FormatWithTimezone::ATOM,
        );
    }

    /**
     * FormatPlus.
     *
     * Local aware formatting works with different formats than php DateTime.
     * It works with unicode date symbols.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
     * @see https://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
     *
     * Best practice it to show the DateTime in the users timezone and locale.
     * That is why we always need to specify these two as function arguments
     *
     **/
    public function formatPlus(
        string $format,
        string $locale,
        DateTimeZone $timeZone,
        ?IntlCalendar $calendar = null,
    ): string {
        $formatter = new IntlDateFormatter(
            locale: $locale,
            dateType: IntlDateFormatter::FULL, // defaults that won't be use since we always provide a pattern
            timeType: IntlDateFormatter::FULL, // defaults that won't be use since we always provide a pattern
            timezone: $timeZone,
            calendar: $calendar ?: IntlCalendar::createInstance($timeZone, $locale),
            pattern: $format,
        );

        $formatted = $formatter->format($this->carbon->toDateTime());
        if (!$formatted) {
            throw InvalidDateTimePlusFormatting::failedFormatting($format, $locale, $timeZone);
        }

        return $formatted;
    }

    public function timestamp(): int
    {
        return $this->carbon->getTimestamp();
    }

    public function timezone(): DateTimeZone
    {
        return $this->carbon->getTimezone();
    }

    public function alterTime(
        int $hour,
        int $minute,
        int $second,
    ): DateTimePlus {
        $carbon = clone $this->carbon;
        $changed = $carbon->setTime($hour, $minute, $second);

        return self::from(
            $changed->toDateTimeImmutable()->format(FormatWithTimezone::ATOM->pattern()),
            FormatWithTimezone::ATOM,
        );
    }

    public function year(): Year
    {
        $year = (int) $this->toPhpDateTime()->format('Y');
        return Year::from(
            $year
        );
    }

    public function month(): Month
    {
        $month = (int) $this->toPhpDateTime()->format('n');
        return Month::from(
            $month
        );
    }

    public function day(): Day
    {
        $day = (int) $this->toPhpDateTime()->format('j');
        return Day::from(
            $day
        );
    }

    public function yearAndMonth(): YearAndMonth
    {
        $year = $this->year();
        $month = $this->month();
        return YearAndMonth::from($year, $month);
    }
}
