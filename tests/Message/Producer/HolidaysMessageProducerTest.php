<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Producer\BirthdayMessageProducer;
use App\Service\Message\Producer\HolidaysMessageProducer;
use Tests\AbstractKernelTestCase;

class HolidaysMessageProducerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testProduceMessages(): void
    {
        $user1 = UserFactory::create();
        $user2 = UserFactory::create();

        $message = MessageFactory::create(
            type: MessageTypeEnum::CHRISTMAS_GREETING,
            user: $user1,
            scheduledAt: new \DateTimeImmutable('today'),
        );
        $message->setStatus(null);
        $message->setProcessedAt(null);

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($message);
        $this->entityManager->flush();


        /** @var HolidaysMessageProducer $test */
        $test = $this->get(HolidaysMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);

        $scheduledDate = (new \DateTimeImmutable('today'))->setTime(18, 0);
        $this->assertSame(date('Ymd 18:00:00'), $scheduledDate->format('Ymd H:i:s'));

        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::CHRISTMAS_GREETING->value,
            'user' => $user1,
            'scheduledAt' => $scheduledDate,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::CHRISTMAS_GREETING->value,
            'user' => $user2,
            'scheduledAt' => $scheduledDate,
        ]);
    }

    public function testMessagesValidation(): void
    {
        $message1 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: (new \DateTimeImmutable('today'))->setTime(9, 0),
        );
        $message1->setStatus(null);
        $message1->setProcessedAt(null);

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $message2->setStatus(MessageStatusEnum::SENT->value);
        $message2->setProcessedAt(new \DateTimeImmutable('now'));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: new \DateTimeImmutable('-1 min'),
        );
        $message3->setStatus(null);
        $message3->setProcessedAt(null);

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::USER_BIRTHDAY_GREET,
            scheduledAt: (new \DateTimeImmutable('today'))->setTime(9, 0),
        );
        $message4->setStatus(MessageStatusEnum::ERROR->value);
        $message4->setProcessedAt(new \DateTimeImmutable('now'));

        /** @var BirthdayMessageProducer $test */
        $test = $this->get(BirthdayMessageProducer::class);
        $this->assertTrue($test->isRelevant($message1));
        $this->assertTrue($test->isWaiting($message1));
        $this->assertFalse($test->isWaiting($message2));
        $this->assertTrue($test->isRelevant($message3));
        $this->assertFalse($test->isRelevant($message4));
    }
}
