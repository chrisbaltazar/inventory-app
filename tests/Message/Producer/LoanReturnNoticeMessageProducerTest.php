<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Producer\LoanReturnNoticeMessageProducer;
use DateTimeImmutable;
use Tests\AbstractKernelTestCase;

class LoanReturnNoticeMessageProducerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testProduceMessages(): void
    {
        $eventOpen = EventFactory::create(returnDate: null);
        $userOnEventOpen = UserFactory::create();
        $loanOnEventOpen = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: null,
            user: $userOnEventOpen,
            event: $eventOpen,
        );

        $eventFuture = EventFactory::create(returnDate: new DateTimeImmutable('+10 days'));
        $userOnEventFuture = UserFactory::create();
        $loanOnEventFuture = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: null,
            user: $userOnEventFuture,
            event: $eventFuture,
        );

        $eventPast = EventFactory::create(returnDate: new DateTimeImmutable('-1 day'));
        $userOnEventPast = UserFactory::create();
        $loanOnEventPast = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: null,
            user: $userOnEventPast,
            event: $eventPast,
        );

        $eventComing = EventFactory::create(returnDate: new DateTimeImmutable('+7 days'));
        $userOnEventComing1 = UserFactory::create();
        $loanOnEventComing1 = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: null,
            user: $userOnEventComing1,
            event: $eventComing,
        );
        $userOnEventComingDone = UserFactory::create();
        $loanOnEventComingClosed = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: new \DateTimeImmutable(),
            user: $userOnEventComingDone,
            event: $eventComing,
        );
        $userOnEventComing2 = UserFactory::create();
        $loanOnEventComing2 = LoanFactory::create(
            startDate: new DateTimeImmutable('-3 days'),
            endDate: null,
            user: $userOnEventComing2,
            event: $eventComing,
        );

        $this->persistAll(
            $eventOpen,
            $userOnEventOpen,
            $loanOnEventOpen,
            $eventFuture,
            $userOnEventFuture,
            $loanOnEventFuture,
            $eventPast,
            $userOnEventPast,
            $loanOnEventPast,
            $eventComing,
            $userOnEventComing1,
            $loanOnEventComing1,
            $userOnEventComingDone,
            $loanOnEventComingClosed,
            $userOnEventComing2,
            $loanOnEventComing2,
        );

        $test = $this->get(LoanReturnNoticeMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_RETURN_NOTICE->value,
            'user' => $userOnEventComing1,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_RETURN_NOTICE->value,
            'user' => $userOnEventComing2,
        ]);

        // Re-run
        $test->produce();
        $this->assertDatabaseCount(2, Message::class);
    }

    public function testMessagesValidation(): void
    {
        /** @var LoanReturnNoticeMessageProducer $test */
        $test = $this->get(LoanReturnNoticeMessageProducer::class);

        $message1 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_NOTICE,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(9, 0)
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message1));
        $this->assertTrue($test->isWaiting($message1));

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_NOTICE,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::SENT->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertTrue($test->isRelevant($message2));
        $this->assertFalse($test->isWaiting($message2));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_NOTICE,
            scheduledAt: new DateTimeImmutable('-1 day'),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertFalse($test->isRelevant($message3));
        $this->assertFalse($test->isWaiting($message3));

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_NOTICE,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::ERROR->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertFalse($test->isRelevant($message4));
        $this->assertFalse($test->isWaiting($message4));
    }
}
