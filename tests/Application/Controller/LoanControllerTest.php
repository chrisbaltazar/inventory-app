<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\InventoryFactory;
use App\DataFixtures\Factory\ItemFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Enum\RegionEnum;
use App\Tests\AbstractWebTestCase;
use App\Tests\Trait\WithUserSession;

class LoanControllerTest extends AbstractWebTestCase
{
    use WithUserSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testNewLoanPage(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create();
        $inventory = InventoryFactory::create();
        $item = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item->addInventory($inventory);

        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $this->asUser($this->client, $user)->request(
            'GET',
            '/loan/new/1/1?region=' . RegionEnum::ACCESORIOS->value
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, 'table.table tr');
        $this->assertSelectorCount(2, 'table.table select option');
    }

    public function testStoreLoanOK(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create();
        $item1 = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item1->addInventory(InventoryFactory::create(quantity: 2));
        $item2 = ItemFactory::create(RegionEnum::ENSAYO->value);
        $item2->addInventory(InventoryFactory::create(quantity: 1));

        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->persist($item1);
        $this->entityManager->persist($item2);
        $this->entityManager->flush();

        $this->asUser($this->client, $user)->request('POST', '/loan/store', [
            'loan' => [
                'event' => $event->getId(),
                'user' => $user->getId(),
                'item' => [
                    sprintf(
                        '%d|%s',
                        $item1->getInventory()->first()->getId(),
                        $item1->getInventory()->first()->getSize()
                    ),
                    sprintf(
                        '%d|%s',
                        $item2->getInventory()->first()->getId(),
                        $item2->getInventory()->first()->getSize()
                    ),
                ],
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }
}
