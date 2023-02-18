<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use Psr\Clock\ClockInterface;

interface ClockPlusInterface extends ClockInterface
{
    public function nowPlus(): DateTimePlus;
}
