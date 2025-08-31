<?php

namespace App\Tests\Trait;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait WithUserSession
{
    public function withUser(KernelBrowser $client, string $userEmail): KernelBrowser
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => $userEmail]);

        return $this->asUser($client, $testUser);
    }

    public function asUser(KernelBrowser $client, User $user): KernelBrowser
    {
        $client->loginUser($user);

        return $client;
    }
}