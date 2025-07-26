<?php

namespace App\Service\Database;

use App\Entity\Contract\CreatedStampInterface;
use App\Entity\Contract\UpdatedStampInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
class DoctrineEntityTimestampsListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->handleCreated($entity);
        $this->handleUpdated($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->handleUpdated($entity);
    }

    private function handleCreated(object $entity): void
    {
        if (!$entity instanceof CreatedStampInterface) {
            return;
        }

        if ($entity->getCreatedAt()) {
            return;
        }

        $entity->setCreatedAt(new \DateTimeImmutable());
    }

    private function handleUpdated(object $entity): void
    {
        if (!$entity instanceof UpdatedStampInterface) {
            return;
        }

        $entity->setUpdatedAt(new \DateTimeImmutable());
    }
}