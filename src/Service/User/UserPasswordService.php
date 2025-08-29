<?php

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordService
{
    const PASSWORD_MIN_LENGTH = 5;

    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function __invoke(User $user, string $pwd): string
    {
        $this->applyPasswordRules($pwd);

        return $this->hasher->hashPassword($user, $pwd);
    }

    private function applyPasswordRules(string $pwd): void
    {
        if (strlen($pwd) < self::PASSWORD_MIN_LENGTH) {
            throw new \UnexpectedValueException('Password too short, min required ' . self::PASSWORD_MIN_LENGTH);
        }

        if (count(array_unique(str_split($pwd))) === 1) {
            throw new \UnexpectedValueException('Password must contain different characters');
        }
    }
}