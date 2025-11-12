<?php

namespace Tests\Controller;

use App\DataFixtures\Factory\UserFactory;
use App\Service\Message\Channel\Sms\SMSProviderInterface;
use tests\AbstractWebTestCase;

class LoginControllerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testRecoverAccessValid()
    {
        $this->markTestSkipped('Not working yet');

        $user = UserFactory::create();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->once())->method('send');

        static::getContainer()->set(SMSProviderInterface::class, $smsProvider);
//        $this->client->getContainer()->set(SMSProviderInterface::class, $smsProvider);
        $this->client->request('GET', '/recover-access');
        $this->client->submitForm('Enviar', [
            'recover_password[email]' => $user->getEmail()
        ]);

        self::assertResponseRedirects('/login/code');
    }
}