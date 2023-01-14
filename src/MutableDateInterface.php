<?php

declare(strict_types=1);

namespace Tactics\DateTime;

/**
 * Interface for dates that allow mutations (adding days, ...)
 */
interface MutableDateInterface extends DateInterface
{
    public function add($years = 0, $months = 0, $days = 0): MutableDateInterface;
}
