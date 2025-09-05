<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\InventoryFactory;
use App\DataFixtures\Factory\ItemFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Inventory;
use App\Entity\Item;
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
}
