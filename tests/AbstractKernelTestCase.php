<?php

namespace Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Trait\WithDatabase;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    use WithDatabase;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = $this->get(EntityManagerInterface::class);
    }

    protected function get(string $key): mixed
    {
        return static::getContainer()->get($key);
    }

    protected function set(string $key, mixed $service): void
    {
        static::getContainer()->set($key, $service);
    }

    protected function persistAll(...$entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}