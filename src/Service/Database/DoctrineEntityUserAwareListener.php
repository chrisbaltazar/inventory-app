<?php

namespace App\Service\Database;

use App\Entity\User;
use App\Entity\UserAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
class DoctrineEntityUserAwareListener
{

    public function __construct(private readonly Security $security)
    {
    }


    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->handleUpdated($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->handleUpdated($entity);
    }


    private function handleUpdated(object $entity): void
    {
        if (!$entity instanceof UserAwareInterface) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $entity->setUpdatedBy($user);
    }
}