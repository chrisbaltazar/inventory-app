<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Event;
use App\Tests\AbstractWebTestCase;
use App\Tests\Trait\WithUserSession;

class EventControllerTest extends AbstractWebTestCase
{
    use WithUserSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testListEvents(): void
    {
        $event = EventFactory::create();
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->forNewUser($this->client, ['roles' => ['ROLE_ADMIN']])->request('GET', '/event/');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'table tbody tr');
    }

    public function testSaveNewEvent(): void
    {
        $this->forNewUser($this->client, ['roles' => ['ROLE_ADMIN']])->request('GET', '/event/new');

        $this->client->submitForm('Guardar', [
            'event[name]' => 'Event Foo',
            'event[date]' => '2025-01-01',
        ]);

        self::assertResponseRedirects('/event/new');
        $this->assertDatabaseCount(1, Event::class);
    }
}
