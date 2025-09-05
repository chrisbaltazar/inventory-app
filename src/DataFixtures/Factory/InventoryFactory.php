<?php

namespace App\DataFixtures\Factory;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Enum\SizeEnum;

class InventoryFactory extends AbstractFactory
{
    public static function create(
        string $size = null,
        string $description = null,
        int $quantity = null,
        string $comments = null,
        Item $item = null,
    ): Inventory {
        $inventory = new Inventory();
        $inventory->setSize($size ?? self::faker()->randomElement(SizeEnum::values()));;
        $inventory->setDescription($description ?? self::faker()->words(3, true));
        $inventory->setQuantity($quantity ?? self::faker()->numberBetween(1, 10));
        $inventory->setComments($comments ?? self::faker()->paragraph);
        $inventory->setItem($item ?? ItemFactory::create());

        return $inventory;
    }
}