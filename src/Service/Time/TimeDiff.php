<?php

declare(strict_types=1);

namespace App\Service\Time;

trait TimeDiff
{
    public function getDiffHours(
        \DateTimeImmutable $datetime,
        \DateTimeImmutable $baseline = new \DateTimeImmutable('now'),
    ): int {
        $diff = $baseline->diff($datetime);

        return $this->getDiffDays($datetime, $baseline) + $diff->h;
    }

    public function getDiffDays(
        \DateTimeImmutable $datetime,
        \DateTimeImmutable $baseline = new \DateTimeImmutable('now'),
    ): int {
        $diff = $baseline->diff($datetime);

        return (int) $diff->days;
    }
}