<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setPassword('123456');
        $admin->setRoles(['ROLE_ADMIN']);

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setPassword('123456');

        $manager->persist($admin);
        $manager->persist($user);

        $manager->flush();
    }
}
