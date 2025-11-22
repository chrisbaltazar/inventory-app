<?php

namespace App\Service\Time;

class ClockService implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }

    public function tomorrow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('tomorrow');
    }

    public function yesterday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('yesterday');
    }
}