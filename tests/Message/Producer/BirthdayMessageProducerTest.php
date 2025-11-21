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
        $user1 = UserFactory::create(birthday: new \DateTime('today'));
        $user2 = UserFactory::create(birthday: new \DateTime('today'));
        $user3 = UserFactory::create(birthday: new \DateTime('-1 day'));
        $admin = UserFactory::admin();

        $message = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            user: $user2,
            content: $user2->getName(),
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $message->setStatus(null);
        $message->setProcessedAt(null);

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($user3);
        $this->entityManager->persist($admin);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        /** @var BirthdayMessageProducer $test */
        $test = $this->get(BirthdayMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(3, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::USER_BIRTHDAY_GREET->value,
            'user' => $user1,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF->value,
            'user' => $admin,
        ]);
    }

    public function testMessageIsRelevant(): void
    {
        $message1 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $message1->setStatus(null);
        $message1->setProcessedAt(null);

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $message2->setStatus(MessageStatusEnum::SENT->value);
        $message2->setProcessedAt(new \DateTimeImmutable('now'));

        /** @var BirthdayMessageProducer $test */
        $test = $this->get(BirthdayMessageProducer::class);
        $this->assertTrue($test->canBeCreated($message1));
        $this->assertFalse($test->canBeCreated($message2));
    }
}
