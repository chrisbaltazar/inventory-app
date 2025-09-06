<?php

namespace App\DataFixtures\Factory;

use App\Entity\Item;
use App\Enum\GenderEnum;

class ItemFactory extends AbstractFactory
{
    public static function create(
        string $region = null,
        string $name = null,
        string $gender = null,
    ): Item {
        $item = new Item();
        $item->setRegion($region ?? self::faker()->name);
        $item->setName($name ?? self::faker()->name);
        $item->setGender($gender ?? self::faker()->randomElement(GenderEnum::names()));

        return $item;
    }
}