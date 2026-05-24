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
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\AbstractKernelTestCase;

class MessageManagerServiceTest extends AbstractKernelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    #[Test]
    #[DataProvider('provide_process_all_messages')]
    public function process_all_pending_messages(MessageTypeEnum $messageType): void
    {
        $user = UserFactory::create(phoneNumber: '+34111111111');
        $message1 = MessageFactory::create(
            type: $messageType,
            user: $user,
            content: 'Present',
            scheduledAt: new \DateTimeImmutable('now'),
        );
        $message1->setRecipient(null);
        $message1->setStatus(null);
        $message1->setProcessedAt(null);

        $message2 = MessageFactory::create(
            type: $messageType,
            user: $user,
            content: 'Past',
            scheduledAt: new \DateTimeImmutable('-1 hour'),
        );
        $message2->setRecipient(null);
        $message2->setStatus(null);
        $message2->setProcessedAt(null);

        $message3 = MessageFactory::create(
            type: $messageType,
            user: $user,
            content: 'Future',
            scheduledAt: new \DateTimeImmutable('+1 hour'),
        );
        $message3->setRecipient(null);
        $message3->setStatus(null);
        $message3->setProcessedAt(null);

        $this->persistAll($user, $message1, $message2, $message3);

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
        $producer1
            ->expects($this->atLeast(1))
            ->method('isRegistered')
            ->willReturn(true);
        $producer1
            ->expects($this->atLeast(1))
            ->method('isExpired')
            ->willReturnCallback(function (Message $message) {
                return match ($message->getContent()) {
                    'Past' => true,
                    default => false,
                };
            });

        $iterator = $this->getIteratorWith([$producer1]);
        $test = new MessageManagerService($repository, $eventDispatcher, $iterator);
        $test->processAllPending();

        $message1 = $repository->find($message1->getId());
        $this->assertSame(
            MessageStatusEnum::SENT->value,
            $message1->getStatus(),
            'Maybe the message type is not handled?',
        );
        $this->assertNotNull($message1->getProcessedAt());
        $message2 = $repository->find($message2->getId());
        $this->assertNull($message2->getStatus());
        $this->assertNull($message2->getProcessedAt());
        $message3 = $repository->find($message3->getId());
        $this->assertNull($message3->getStatus());
        $this->assertNull($message3->getProcessedAt());
    }

    public static function provide_process_all_messages(): array
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
        $producer->method('isRegistered')->willReturn(true);
        $producer->method('isExpired')->willReturn(false);
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
