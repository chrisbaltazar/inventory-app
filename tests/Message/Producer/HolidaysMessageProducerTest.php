<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Producer\HolidaysMessageProducer;
use App\Service\Time\ClockInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractKernelTestCase;

class HolidaysMessageProducerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    #[DataProvider('provideProduceMessages')]
    public function testProduceMessages(
        MessageTypeEnum $messageType,
        \DateTimeImmutable $today,
        int $expectedHour,
        int $expectedCount,
    ): void {
        $user1 = UserFactory::create();
        $user2 = UserFactory::create();
        $user3 = UserFactory::create();
        // Pending message to send
        $message1 = MessageFactory::create(
            type: $messageType,
            user: $user1,
            scheduledAt: $today,
        );
        $message1->setStatus(null);
        $message1->setProcessedAt(null);
        // Already sent message
        $message2 = MessageFactory::create(
            type: $messageType,
            user: $user3,
            scheduledAt: $today,
        );
        $message2->setStatus(MessageStatusEnum::SENT->value);
        $message2->setProcessedAt(new DateTimeImmutable('-1 hour'));

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($user3);
        $this->entityManager->persist($message1);
        $this->entityManager->persist($message2);
        $this->entityManager->flush();

        $clock = $this->createMock(ClockInterface::class);
        $clock->method('today')->willReturn($today);
        $this->set(ClockInterface::class, $clock);

        /** @var HolidaysMessageProducer $test */
        $test = $this->get(HolidaysMessageProducer::class);
        $test->produce();

        $scheduledDate = $today->setTime($expectedHour, 0);
        // Adding manually the existing message
        $this->assertDatabaseCount($expectedCount + 1, Message::class);
        if ($expectedCount > 1) {
            $this->assertDatabaseEntity(Message::class, [
                'type' => $messageType->value,
                'user' => $user2,
                'scheduledAt' => $scheduledDate,
            ]);
        }
    }

    public static function provideProduceMessages(): array
    {
        return [
            'xmas' => [
                'messageType' => MessageTypeEnum::CHRISTMAS_GREETING,
                'today' => new \DateTimeImmutable(date('Y') . '-12-24 00:00:00'),
                'expectedHour' => 18,
                'expectedCount' => 2,
            ],
            'new_year' => [
                'messageType' => MessageTypeEnum::NEW_YEAR_GREETING,
                'today' => new \DateTimeImmutable(date('Y') . '-12-31 00:00:00'),
                'expectedHour' => 22,
                'expectedCount' => 2,
            ],
            'other_day' => [
                'messageType' => MessageTypeEnum::CHRISTMAS_GREETING,
                'today' => new \DateTimeImmutable(date('Y') . '-10-01 00:00:00'),
                'expectedHour' => 18,
                'expectedCount' => 1,
            ],
            'another_day' => [
                'messageType' => MessageTypeEnum::NEW_YEAR_GREETING,
                'today' => new \DateTimeImmutable(date('Y') . '-10-01 00:00:00'),
                'expectedHour' => 18,
                'expectedCount' => 1,
            ],
        ];
    }

    public function testMessagesValidation(): void
    {
        /** @var HolidaysMessageProducer $test */
        $test = $this->get(HolidaysMessageProducer::class);

        $message1 = MessageFactory::create(
            type: MessageTypeEnum::CHRISTMAS_GREETING,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(18, 0),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message1));
        $this->assertTrue($test->isWaiting($message1));

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::NEW_YEAR_GREETING,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::SENT->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertTrue($test->isRelevant($message2));
        $this->assertFalse($test->isWaiting($message2));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::CHRISTMAS_GREETING,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message3));
        $this->assertTrue($test->isWaiting($message3));

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::NEW_YEAR_GREETING,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(22, 0),
        )
            ->setStatus(MessageStatusEnum::ERROR->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertFalse($test->isRelevant($message4));
        $this->assertFalse($test->isWaiting($message4));

        $message5 = MessageFactory::create(
            type: MessageTypeEnum::NEW_YEAR_GREETING,
            scheduledAt: (new DateTimeImmutable('tomorrow'))->setTime(22, 0),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertFalse($test->isRelevant($message5));
        $this->assertFalse($test->isWaiting($message5));
    }
}
