<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    protected function refreshDatabase(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    protected function assertDatabaseCount(int $count, string $entityClass): void
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $result = $repository->findAll();

        self::assertCount($count, $result);
    }

    protected function assertDatabaseEntity(string $entityClass, array $data): void
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $result = $repository->findOneBy($data);

        self::assertNotNull($result);
    }

    protected function assertResponseContains(string $text): void
    {
        $this->assertStringContainsString($text, $this->client->getResponse()->getContent());
    }
}