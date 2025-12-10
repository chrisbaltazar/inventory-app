<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Producer\BirthdayMessageProducer;
use Tests\AbstractKernelTestCase;

class BirthdayMessageProducerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testProduceMessages(): void
    {
        $user1 = UserFactory::create(birthday: new \DateTime('today')); // HBD (1)
        $user2 = UserFactory::create(birthday: new \DateTime('today')); // Pending not sent (2)
        $user3 = UserFactory::create(birthday: new \DateTime('-1 day')); // No HBD
        $user4 = UserFactory::create(birthday: new \DateTime('today')); // Existing and sent (3)
        $admin = UserFactory::admin(); // Notif (4)

        $pendingMessage = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            user: $user2,
            content: $user2->getName(),
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $pendingMessage->setStatus(null);
        $pendingMessage->setProcessedAt(null);

        $existingMessage = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            user: $user4,
            content: $user2->getName(),
            scheduledAt: new \DateTimeImmutable('-1 hour'),
        );
        $existingMessage->setStatus(MessageStatusEnum::SENT->value);
        $existingMessage->setProcessedAt(new \DateTimeImmutable('-1 min'));

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($user3);
        $this->entityManager->persist($user4);
        $this->entityManager->persist($admin);
        $this->entityManager->persist($pendingMessage);
        $this->entityManager->persist($existingMessage);
        $this->entityManager->flush();

        /** @var BirthdayMessageProducer $test */
        $test = $this->get(BirthdayMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(4, Message::class);

        $scheduledDate = (new \DateTimeImmutable('today'))->setTime(9, 0);
        $this->assertSame(date('Ymd 09:00:00'), $scheduledDate->format('Ymd H:i:s'));

        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::USER_BIRTHDAY_GREET->value,
            'user' => $user1,
            'scheduledAt' => $scheduledDate,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF->value,
            'user' => $admin,
            'scheduledAt' => $scheduledDate,
        ]);
    }

    public function testMessagesValidation(): void
    {
        /** @var BirthdayMessageProducer $test */
        $test = $this->get(BirthdayMessageProducer::class);

        $message1 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: (new \DateTimeImmutable('today'))->setTime(9, 0),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message1));
        $this->assertTrue($test->isWaiting($message1));

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::SENT->value)
            ->setProcessedAt(new \DateTimeImmutable('now'));

        $this->assertTrue($test->isRelevant($message2));
        $this->assertFalse($test->isWaiting($message2));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message3));
        $this->assertTrue($test->isWaiting($message3));

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: (new \DateTimeImmutable('today'))->setTime(9, 0),
        )
            ->setStatus(MessageStatusEnum::ERROR->value)
            ->setProcessedAt(new \DateTimeImmutable('now'));

        $this->assertFalse($test->isRelevant($message4));
        $this->assertFalse($test->isWaiting($message4));
    }
}
