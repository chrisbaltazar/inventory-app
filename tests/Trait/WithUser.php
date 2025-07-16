<?php

namespace App\Tests\Trait;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait WithUser
{
    public function withUser(KernelBrowser $client, string $userEmail): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => $userEmail]);

        $client->loginUser($testUser);
    }
}