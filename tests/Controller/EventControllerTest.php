<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\Trait\WithUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    use WithUser;

    public function testSomething(): void
    {
        $client = static::createClient();

        $this->withUser($client, 'admin@test.com');

        $client->request('GET', '/event/');

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('h1', 'Eventos');
    }
}
