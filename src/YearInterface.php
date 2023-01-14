<?php

declare(strict_types=1);

namespace Tactics\DateTime;

use DateTimeImmutable;

interface YearInterface
{
    public function firstDay() : DateTimeImmutable;

    public function lastDay() : DateTimeImmutable;

    public function previous(): YearInterface;

    public function next(): YearInterface;
}
