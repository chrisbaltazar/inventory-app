<?php

namespace Tests\Controller;

use App\Controller\LoginController;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\User;
use App\Service\Message\Channel\Sms\SMSProviderInterface;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\AbstractWebTestCase;
use Tests\Trait\WithRequestSession;

class LoginControllerTest extends AbstractWebTestCase
{
    use WithRequestSession;

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
        self::assertGreaterThan(new DateTime(), $user->getCodeExpiration());
        self::assertResponseRedirects('/login/code');
    }

    public function testRecoverAccessExisting()
    {
        $user = UserFactory::create();
        $user->setAccessCode('123456');
        $user->setCodeExpiration(new DateTimeImmutable('+5 min'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->never())->method('send');

        $this->client->getContainer()->set(SMSProviderInterface::class, $smsProvider);
        $this->client->request('GET', '/recover-access');
        $this->client->submitForm('Enviar', [
            'recover_password[email]' => $user->getEmail(),
        ]);

        self::assertGreaterThan(new DateTime(), $user->getCodeExpiration());
        self::assertResponseRedirects('/login/code');
    }

    public function testUserCodeSuccessfull()
    {
        $user = UserFactory::create();
        $user->setAccessCode('123456');
        $user->setCodeExpiration(new DateTimeImmutable('+5 min'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $session = $this->createSession($this->client);
        $session->set(LoginController::USER_ACCESS_ID, $user->getId());
        $session->save();

        $csrfToken = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfToken->expects($this->once())->method('isTokenValid')->willReturn(true);
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfToken);

        $this->client->request('GET', '/login/code');
        $this->client->submitForm('Verificar', [
            'csrf_token' => 'token',
            'code' => [1, 2, 3, 4, 5, 6],
        ]);

        self::assertResponseRedirects("/user/{$user->getId()}/password");
    }

    public function testUserCodeExpired()
    {
        $user = UserFactory::create();
        $user->setAccessCode('123456');
        $user->setCodeExpiration(new DateTimeImmutable('-1 min'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $session = $this->createSession($this->client);
        $session->set(LoginController::USER_ACCESS_ID, $user->getId());
        $session->save();

        $csrfToken = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfToken->expects($this->once())->method('isTokenValid')->willReturn(true);
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfToken);

        $this->client->request('GET', '/login/code');
        $this->client->submitForm('Verificar', [
            'csrf_token' => 'token',
            'code' => [1, 2, 3, 4, 5, 6],
        ]);

        self::assertResponseRedirects('/login');
    }

    public function testUserCodeError()
    {
        $user = UserFactory::create();
        $user->setAccessCode('123456');
        $user->setCodeExpiration(new DateTimeImmutable('+5 min'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $session = $this->createSession($this->client);
        $session->set(LoginController::USER_ACCESS_ID, $user->getId());
        $session->save();

        $csrfToken = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfToken->expects($this->once())->method('isTokenValid')->willReturn(true);
        $this->client->getContainer()->set('security.csrf.token_manager', $csrfToken);

        $this->client->request('GET', '/login/code');
        $this->client->submitForm('Verificar', [
            'csrf_token' => 'token',
            'code' => [1, 1, 1, 1, 1, 1],
        ]);

        self::assertResponseRedirects('/login/code');
    }
}