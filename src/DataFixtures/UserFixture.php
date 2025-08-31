<?php

namespace App\DataFixtures;

use App\DataFixtures\Factory\UserFactory;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = UserFactory::create(
            name: 'Mr. Admin',
            email: 'admin@test.com',
            roles: ['ROLE_ADMIN']
        );
        $admin->setPassword($this->hasher->hashPassword($admin, '123456'));

        $user = UserFactory::create(
            name: 'Mr. User',
            email: 'user@test.com'
        );
        $user->setPassword($this->hasher->hashPassword($user, '123456'));

        $manager->persist($admin);
        $manager->persist($user);
        $manager->flush();
    }
}
