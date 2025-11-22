<?php

namespace Tests\Service\Message;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Channel\Sms\SMSProviderInterface;
use App\Service\Message\MessageManagerService;
use App\Service\Message\Producer\MessageProducerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\AbstractKernelTestCase;

class MessageManagerServiceTest extends AbstractKernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    #[DataProvider('provideProcessAllMessages')]
    public function testProcessAllPendingMessages(MessageTypeEnum $messageType): void
    {
        $user = UserFactory::create(phoneNumber: '+34111111111');
        $message1 = MessageFactory::create(
            type: $messageType,
            user: $user,
            content: 'Message content...',
            scheduledAt: new \DateTimeImmutable('now'),
        );
        $message1->setRecipient(null);
        $message1->setStatus(null);
        $message1->setProcessedAt(null);

        $message2 = MessageFactory::create(
            user: $user,
            scheduledAt: new \DateTimeImmutable('+1 hour'),
        );
        $message2->setRecipient(null);
        $message2->setStatus(null);
        $message2->setProcessedAt(null);

        $this->entityManager->persist($user);
        $this->entityManager->persist($message1);
        $this->entityManager->persist($message2);
        $this->entityManager->flush();

        $repository = $this->entityManager->getRepository(Message::class);
        $eventDispatcher = $this->get(EventDispatcherInterface::class);
        $smsProvider = $this->createMock(SMSProviderInterface::class);
        $smsProvider->expects($this->once())->method('send')->willReturnCallback(
            function ($number, $sender, $content) use ($message1, $user) {
                $this->assertSame($user->getPhone(), $number);
                $this->assertNotEmpty($sender);
                $this->assertStringContainsString($message1->getContent(), $content);
            },
        );
        $this->set(SMSProviderInterface::class, $smsProvider);

        $producer1 = $this->createMock(MessageProducerInterface::class);
        $producer1->expects($this->once())->method('isWaiting')->willReturn(true);
        $producer2 = $this->createMock(MessageProducerInterface::class);
        $producer2->expects($this->never())->method('isWaiting');
        $iterator = $this->getIteratorWith([$producer1, $producer2]);

        $test = new MessageManagerService($repository, $eventDispatcher, $iterator);
        $test->processAllPending();

        $message1 = $repository->find($message1->getId());
        $this->assertSame(MessageStatusEnum::SENT->value, $message1->getStatus());
        $this->assertNotNull($message1->getProcessedAt());
    }

    public static function provideProcessAllMessages(): array
    {
        return array_map(fn($type) => [$type], MessageTypeEnum::cases());
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

        $producer = $this->createMock(MessageProducerInterface::class);
        $producer->expects($this->once())->method('isWaiting')->willReturn(true);
        $iterator = $this->getIteratorWith([$producer]);

        $test = new MessageManagerService($repository, $eventDispatcher, $iterator);
        $test->processAllPending();

        $message = $repository->find($message->getId());
        $this->assertSame(MessageStatusEnum::ERROR->value, $message->getStatus());
        $this->assertNotNull($message->getProcessedAt());
    }

    private function getIteratorWith(array $iteratorItems)
    {
        $iterator = $this->getMockBuilder(\IteratorAggregate::class)->getMock();
        $iterator->method('getIterator')->willReturn(new \ArrayIterator($iteratorItems));

        return $iterator;
    }

}
