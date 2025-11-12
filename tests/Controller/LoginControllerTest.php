<?php

namespace Tests\Controller;

use App\DataFixtures\Factory\UserFactory;
use App\Entity\User;
use App\Service\Message\Channel\Sms\SMSProviderInterface;
use Tests\AbstractWebTestCase;

class LoginControllerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->client->disableReboot();
    }

    public function testRecoverAccessValid()
    {
        $user = UserFactory::create();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->once())->method('send');
        $userRepository = $this->entityManager->getRepository($user::class);

        $this->client->getContainer()->set(SMSProviderInterface::class, $smsProvider);
        $this->client->request('GET', '/recover-access');
        $this->client->submitForm('Enviar', [
            'recover_password[email]' => $user->getEmail(),
        ]);

        /** @var User $user */
        $user = $userRepository->find($user->getId());
        self::assertNotNull($user->getAccessCode());
        self::assertNotNull($user->getCodeExpiration());
        self::assertGreaterThan(new \DateTime(), $user->getCodeExpiration());
        self::assertResponseRedirects('/login/code');
    }
}