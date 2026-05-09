<?php

declare(strict_types=1);

namespace App\Service\Time;

trait TimeDiff
{
    public function getDiffHours(
        \DatetimeInterface $datetime,
        \DatetimeInterface $baseline = new \DateTimeImmutable('now'),
    ): int {
        $diff = $baseline->diff($datetime);

        return (int) $diff->days * 24 + $diff->h;
    }
}