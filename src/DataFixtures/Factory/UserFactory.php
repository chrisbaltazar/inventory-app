<?php

namespace App\DataFixtures\Factory;

use App\Entity\User;

class UserFactory extends AbstractFactory
{
    public static function create(
        string $name = null,
        string $email = null,
        string $password = null,
        array $roles = null,
    ): User
    {
        $user = new User();
        $user->setName($name ?? self::faker()->name);
        $user->setEmail($email ?? self::faker()->email);
        $user->setPassword($password ?? self::faker()->password);
        $user->setRoles($roles ?? []);

        return $user;
    }
}