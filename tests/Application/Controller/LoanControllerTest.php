<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\InventoryFactory;
use App\DataFixtures\Factory\ItemFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Item;
use App\Entity\Loan;
use App\Enum\LoanStatusEnum;
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
        $item1->addInventory(InventoryFactory::create(quantity: 1));
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
        $this->assertDatabaseCount(2, Loan::class);
    }

    public function testStoreLoanErrorNoItems(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create();
        $item1 = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item1->addInventory(InventoryFactory::create(quantity: 1));
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
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseContains('No items found');
        $this->assertDatabaseCount(0, Loan::class);
    }

    public function testStoreLoanErrorNoInventory(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create();
        $item1 = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item1->addInventory(InventoryFactory::create(quantity: 1));
        $item2 = ItemFactory::create(RegionEnum::ENSAYO->value);
        $item2->addInventory(InventoryFactory::create(quantity: 1));
        $loan = LoanFactory::create(
            startDate: new \DateTimeImmutable(),
            endDate: null,
            user: $user,
            event: $event,
            item: $item1,
            inventory: $item1->getInventory()->first(),
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );

        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->persist($item1);
        $this->entityManager->persist($item2);
        $this->entityManager->persist($loan);
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

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseContains('Not enough availability');
        // Previously exising loan
        $this->assertDatabaseCount(1, Loan::class);
    }

    public function testStoreLoanErrorOpenLoanSameItem(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create();
        $item1 = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item1->addInventory(InventoryFactory::create(quantity: 2));
        $item2 = ItemFactory::create(RegionEnum::ENSAYO->value);
        $item2->addInventory(InventoryFactory::create(quantity: 1));
        $loan = LoanFactory::create(
            startDate: new \DateTimeImmutable(),
            endDate: null,
            user: $user,
            event: $event,
            item: $item1,
            inventory: $item1->getInventory()->first(),
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );

        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->persist($item1);
        $this->entityManager->persist($item2);
        $this->entityManager->persist($loan);
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

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseContains('User with open loan');
        // Previously exising loan
        $this->assertDatabaseCount(1, Loan::class);
    }
}
