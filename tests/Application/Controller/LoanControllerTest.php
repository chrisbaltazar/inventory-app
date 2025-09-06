<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\InventoryFactory;
use App\DataFixtures\Factory\ItemFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\UserFactory;
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
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
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
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
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
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
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
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
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
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
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

    public function testShowLoanUser(): void
    {
        $user = UserFactory::admin();
        $event1 = EventFactory::create(
            returnDate: new \DateTimeImmutable('-1 day'),
            date: new \DateTimeImmutable('-3 days'),
        );
        $event2 = EventFactory::create(
            returnDate: null,
            date: new \DateTimeImmutable('now')
        );
        $item1 = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item1->addInventory(InventoryFactory::create(quantity: 1));
        $item2 = ItemFactory::create(RegionEnum::ENSAYO->value);
        $item2->addInventory(InventoryFactory::create(quantity: 1));
        $loan1 = LoanFactory::create(
            startDate: new \DateTimeImmutable(),
            endDate: null,
            user: $user,
            event: $event1,
            item: $item1,
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );
        $loan2 = LoanFactory::create(
            startDate: new \DateTimeImmutable('now'),
            endDate: null,
            user: $user,
            event: $event2,
            item: $item2,
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );

        $this->entityManager->persist($user);
        $this->entityManager->persist($event1);
        $this->entityManager->persist($event2);
        $this->entityManager->persist($item1);
        $this->entityManager->persist($item2);
        $this->entityManager->persist($loan1);
        $this->entityManager->persist($loan2);
        $this->entityManager->flush();

        $this->asUser($this->client, $user)->request('GET', '/loan/user/' . $user->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3.event-open_title', 'Eventos abiertos');
        $this->assertSelectorTextContains('h3.event-closed_title', 'Eventos cerrados');
        $this->assertSelectorCount(1, 'div.event-open_wrapper');
        $this->assertSelectorCount(1, 'div.event-closed_wrapper');
        $this->assertSelectorCount(0, 'div.event-open_wrapper table tr.table-danger');
        $this->assertSelectorCount(1, 'div.event-closed_wrapper table tr.table-danger');
    }

    public function testShowLoanItem(): void
    {
        $user = UserFactory::admin();
        $item = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item->addInventory(InventoryFactory::create(quantity: 1));
        $event1 = EventFactory::create(
            returnDate: new \DateTimeImmutable('-1 day'),
            date: new \DateTimeImmutable('-3 days'),
        );
        $event2 = EventFactory::create(
            returnDate: null,
            date: new \DateTimeImmutable('now')
        );
        $loan1 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-5 days'),
            endDate: new \DateTimeImmutable('-1 day'),
            user: $user,
            event: $event1,
            item: $item,
            quantity: 1,
            status: LoanStatusEnum::CLOSED,
        );
        $loan2 = LoanFactory::create(
            startDate: new \DateTimeImmutable('now'),
            endDate: null,
            user: $user,
            event: $event2,
            item: $item,
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );

        $this->entityManager->persist($user);
        $this->entityManager->persist($item);
        $this->entityManager->persist($event1);
        $this->entityManager->persist($event2);
        $this->entityManager->persist($loan1);
        $this->entityManager->persist($loan2);
        $this->entityManager->flush();

        $crawler = $this->asUser($this->client, $user)->request('GET', '/loan/item/' . $item->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(2, 'table.table tbody tr');
        $buttons = $crawler->filter('button.js-loan-comment');
        $this->assertStringContainsString('ABIERTO', $buttons->first()->text());
        $this->assertStringContainsString('CORRECTO', $buttons->last()->text());
    }

    public function testUpdateLoanOK(): void
    {
        $user = UserFactory::admin();
        $event = EventFactory::create(returnDate: new \DateTimeImmutable('now'));
        $item = ItemFactory::create(RegionEnum::ACCESORIOS->value);
        $item->addInventory(InventoryFactory::create(quantity: 1));
        $loan = LoanFactory::create(
            startDate: new \DateTimeImmutable('-1 day'),
            endDate: null,
            user: $user,
            event: $event,
            item: $item,
            quantity: 1,
            status: LoanStatusEnum::OPEN,
        );

        $this->entityManager->persist($user);
        $this->entityManager->persist($event);
        $this->entityManager->persist($item);
        $this->entityManager->persist($loan);
        $this->entityManager->flush();

        $this->asUser($this->client, $user)->request('POST', '/loan/update', [
            'id' => $loan->getId(),
            'loan_return' => [
                'endDate' => (new \DateTimeImmutable('now'))->format('Y-m-d'),
                'status' => LoanStatusEnum::CLOSED->value,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertDatabaseEntity(Loan::class, [
            'id' => $loan->getId(),
            'status' => LoanStatusEnum::CLOSED->value,
        ]);
    }
}
