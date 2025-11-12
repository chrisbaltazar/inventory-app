<?php

namespace Tests\Service\Message;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Service\Message\Channel\Sms\SMSProviderInterface;
use App\Service\Message\MessageManagerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\AbstractKernelTestCase;

class MessageManagerServiceTest extends AbstractKernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testProcessAllPendingMessages(): void
    {
        $user = UserFactory::create(phoneNumber: '+34111111111');
        $message = MessageFactory::create(
            user: $user,
            content: 'Message content...',
            scheduledAt: new \DateTimeImmutable('now'),
        );
        $message->setRecipient(null);
        $message->setStatus(null);
        $message->setProcessedAt(null);

        $this->entityManager->persist($user);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->assertDatabaseCount(1, Message::class);

        $repository = $this->entityManager->getRepository(Message::class);
        $eventDispatcher = $this->get(EventDispatcherInterface::class);
        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->once())->method('send')->willReturnCallback(
            function ($number, $sender, $content) use ($message, $user) {
                $this->assertSame($user->getPhone(), $number);
                $this->assertNotEmpty($sender);
                $this->assertStringContainsString($message->getContent(), $content);
            },
        );
        $this->set(SMSProviderInterface::class, $smsProvider);

        $test = new MessageManagerService($repository, $eventDispatcher);
        $test->processAllPending();

        $message = $repository->find($message->getId());
        $this->assertSame(MessageStatusEnum::SENT->value, $message->getStatus());
        $this->assertNotNull($message->getProcessedAt());
    }

    public function testProcessMessagesForbiddenNumber(): void
    {
        $user = UserFactory::create(phoneNumber: '+11111111111'); // U.S
        $message = MessageFactory::create(
            user: $user,
            content: 'Message content...',
            scheduledAt: new \DateTimeImmutable('now'),
        );
        $message->setRecipient(null);
        $message->setStatus(null);
        $message->setProcessedAt(null);

        $this->entityManager->persist($user);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->assertDatabaseCount(1, Message::class);

        $repository = $this->entityManager->getRepository(Message::class);
        $eventDispatcher = $this->get(EventDispatcherInterface::class);
        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->never())->method('send');
        $this->set(SMSProviderInterface::class, $smsProvider);

        $this->expectException(\UnexpectedValueException::class);
        $test = new MessageManagerService($repository, $eventDispatcher);
        $test->processAllPending();

        $message = $repository->find($message->getId());
        $this->assertSame(MessageStatusEnum::ERROR->value, $message->getStatus());
        $this->assertNotNull($message->getProcessedAt());
    }

}
