<?php

namespace Tests\Trait;

use App\DataFixtures\Factory\UserFactory;
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

    public function forNewUser(KernelBrowser $client, array $data = []): KernelBrowser
    {
        $user = UserFactory::create(...$data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->asUser($client, $user);
    }
}