<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeInterface;

/**
 * Interface for dates that allow evolution to a new date (adding days, ...)
 */
interface EvolvableDateTimeInterface
{
    public function add($years = 0, $months = 0, $days = 0): EvolvableDateTimeInterface;
}
