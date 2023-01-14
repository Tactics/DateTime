<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;
use DateTimeInterface;

interface DateInterface
{
    public function toDateTime(): DateTimeImmutable;

    public function isSame(DateTimeInterface $dateTime): bool;

    public function isBefore(DateTimeInterface $dateTime): bool;

    public function isAfter(DateTimeInterface $dateTime): bool;
}
