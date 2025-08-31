<?php

namespace App\DataFixtures\Factory;

use App\Entity\Event;

class EventFactory extends AbstractFactory
{
    public static function create(
        string $name = null,
        \DateTimeInterface $date = null,
        \DateTimeInterface $deliveryDate = null,
        \DateTimeInterface $returnDate = null,
        bool $public = null,
    ): Event {
        $event = new Event();
        $event->setName($name ?? self::faker()->name);
        $event->setDate($date ?? self::faker()->dateTime);
        $event->setDeliveryDate($deliveryDate ?? self::faker()->dateTime);
        $event->setReturnDate($returnDate ?? self::faker()->dateTime);
        $event->setPublic($public ?? self::faker()->boolean);

        return $event;
    }
}