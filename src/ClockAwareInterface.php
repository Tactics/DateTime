<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Psr\Clock\ClockInterface;

interface ClockAwareInterface extends ClockInterface
{
    public function isFuture(): bool;

    public function isPast(): bool;
}
