<?php

namespace App\DataFixtures\Factory;

use App\Entity\Event;

class EventFactory extends AbstractFactory
{
    public static function create(
        ?\DateTimeInterface $returnDate,
        \DateTimeInterface $date = null,
        \DateTimeInterface $deliveryDate = null,
        string $name = null,
        bool $public = null,
    ): Event {
        $event = new Event();
        $event->setReturnDate($returnDate);
        $event->setDate($date ?? self::faker()->dateTime);
        $event->setDeliveryDate($deliveryDate ?? self::faker()->dateTime);
        $event->setName($name ?? self::faker()->name);
        $event->setPublic($public ?? self::faker()->boolean);

        return $event;
    }
}