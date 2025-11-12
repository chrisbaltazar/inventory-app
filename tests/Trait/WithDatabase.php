<?php

namespace Tests\Trait;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

trait WithDatabase
{

    public function refreshDatabase(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
    }

    public function assertDatabaseCount(int $count, string $entityClass): void
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $result = $repository->findAll();

        self::assertCount($count, $result);
    }

    public function assertDatabaseEntity(string $entityClass, array $data): void
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $result = $repository->findOneBy($data);

        self::assertNotNull($result);
    }

}