<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\EventFactory;
use App\Tests\Trait\WithUserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    use WithUserSession;

    public function testListEvents(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $event = EventFactory::create();
        $entityManager->persist($event);
        $entityManager->flush();

        $this->withUser($client, 'admin@test.com')->request('GET', '/event/');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'table tbody tr');
    }
}
