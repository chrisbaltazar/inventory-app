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
        $message = MessageFactory::create(
            type: $messageType,
            user: $user1,
            scheduledAt: $today,
        );
        $message->setStatus(null);
        $message->setProcessedAt(null);

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $clock = $this->createMock(ClockInterface::class);
        $clock->method('today')->willReturn($today);
        $this->set(ClockInterface::class, $clock);

        /** @var HolidaysMessageProducer $test */
        $test = $this->get(HolidaysMessageProducer::class);
        $test->produce();

        $scheduledDate = $today->setTime($expectedHour, 0);
        $this->assertDatabaseCount($expectedCount, Message::class);
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
                'expectedHour' => 23,
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
        $message1 = MessageFactory::create(
            type: MessageTypeEnum::CHRISTMAS_GREETING,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(18, 0),
        );
        $message1->setStatus(null);
        $message1->setProcessedAt(null);

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::NEW_YEAR_GREETING,
            scheduledAt: new DateTimeImmutable('-1 min'),
        );
        $message2->setStatus(MessageStatusEnum::SENT->value);
        $message2->setProcessedAt(new DateTimeImmutable('now'));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::CHRISTMAS_GREETING,
            scheduledAt: new DateTimeImmutable('-1 min'),
        );
        $message3->setStatus(null);
        $message3->setProcessedAt(null);

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::NEW_YEAR_GREETING,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(23, 0),
        );
        $message4->setStatus(MessageStatusEnum::ERROR->value);
        $message4->setProcessedAt(new DateTimeImmutable('now'));

        /** @var HolidaysMessageProducer $test */
        $test = $this->get(HolidaysMessageProducer::class);
        $this->assertTrue($test->isRelevant($message1));
        $this->assertTrue($test->isWaiting($message1));
        $this->assertFalse($test->isWaiting($message2));
        $this->assertTrue($test->isRelevant($message3));
        $this->assertFalse($test->isRelevant($message4));
    }
}
