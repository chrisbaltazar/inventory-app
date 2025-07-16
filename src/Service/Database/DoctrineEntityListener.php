<?php

namespace App\Service\Database;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Entity;

#[AsDoctrineListener('preUpdate'/*, 500, 'default'*/)]
class DoctrineEntityListener
{
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->handleCreated($entity);
        $this->handleUpdated($entity);
    }

    private function handleCreated(object $entity): void
    {
        if (method_exists($entity, 'getCreatedAt')) {
            $created = $entity->getCreatedAt();
            if ($created) {
                return;
            }
        }

        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt(new \DateTimeImmutable());
        }
    }

    private function handleUpdated(object $entity)
    {
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }
    }
}