<?php

namespace Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Trait\WithDatabase;

abstract class AbstractWebTestCase extends WebTestCase
{
    use WithDatabase;

    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    protected function assertResponseContains(string $text): void
    {
        $this->assertStringContainsString($text, $this->client->getResponse()->getContent());
    }

    protected function persistAll(...$entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

}