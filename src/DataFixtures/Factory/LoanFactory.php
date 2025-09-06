<?php

namespace App\DataFixtures\Factory;

use App\Entity\Event;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Loan;
use App\Entity\User;
use App\Enum\LoanStatusEnum;

class LoanFactory extends AbstractFactory
{
    public static function create(
        \DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate,
        User $user = null,
        Event $event = null,
        Item $item = null,
        Inventory $inventory = null,
        int $quantity = null,
        LoanStatusEnum $status = null,
        string $comments = null,
    ): Loan {
        $loan = new Loan();
        $loan->setStartDate($startDate);
        $loan->setEndDate($endDate);
        $loan->setUser($user ?? UserFactory::create());
        $loan->setEvent($event ?? EventFactory::create());
        $item ??= ItemFactory::create();
        $loan->setItem($item);
        $inventory ??= InventoryFactory::create(item: $item);
        $loan->setInfo(json_encode($inventory->getInfo(), JSON_UNESCAPED_UNICODE));
        $loan->setQuantity($quantity ?? self::faker()->numberBetween(1, 10));
        $loan->setStatus($status->value ?? self::faker()->randomElement(LoanStatusEnum::values()));
        $loan->setComments($comments ?? self::faker()->paragraph);

        return $loan;
    }
}