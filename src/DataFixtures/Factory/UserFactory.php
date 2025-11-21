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
        string $phoneNumber = null,
        \DateTime $birthday = null,
    ): User {
        $user = new User();
        $user->setName($name ?? self::faker()->name);
        $user->setEmail($email ?? self::faker()->email);
        $user->setPassword($password ?? self::faker()->password);
        $user->setRoles($roles ?? []);
        $user->setPhone($phoneNumber ?? sprintf('+34%d', self::faker()->randomNumber(9)));
        $user->setBirthday($birthday ?? self::faker()->dateTimeBetween('-50 years', '-18 years'));

        return $user;
    }

    public static function admin(string $email = null): User
    {
        return self::create(
            email: $email,
            roles: ['ROLE_ADMIN'],
        );
    }
}