<?php

namespace App\Service\Time;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;

    public function today(): \DateTimeImmutable;

    public function tomorrow(): \DateTimeImmutable;

    public function yesterday(): \DateTimeImmutable;
}