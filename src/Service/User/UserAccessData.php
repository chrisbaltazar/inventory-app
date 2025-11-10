<?php

namespace App\Service\User;

class UserAccessData
{

    public function __construct(
        public readonly \DateTimeInterface $expiration,
        public readonly string $userNumber,
    ) {}

}