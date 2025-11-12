<?php

namespace Tests\Trait;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

trait WithDatabase
{

    protected function refreshDatabase(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
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

}