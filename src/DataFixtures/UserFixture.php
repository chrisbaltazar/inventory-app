<?php

namespace App\DataFixtures;

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
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setPassword($this->hasher->hashPassword($admin, '123456'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setName('Mr. Admin');

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setPassword($this->hasher->hashPassword($user, '123456'));
        $user->setName('Ms. Jane Doe');

        $manager->persist($admin);
        $manager->persist($user);

        $manager->flush();
    }
}
