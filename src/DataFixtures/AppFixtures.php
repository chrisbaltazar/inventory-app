<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setEmail('foo@test.com');
        $user1->setName('John Doe');
        $user1->setPhone('0123456789');
        $user1->setRoles(['ROLE_ADMIN']);
        $user1->setPassword($this->hasher->hashPassword($user1, '123'));

        $user2 = new User();
        $user2->setEmail('bar@test.com');
        $user2->setName('Jane Doe');
        $user2->setPhone('0123456789');
        $user2->setPassword($this->hasher->hashPassword($user1, '123'));

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->flush();
    }
}
