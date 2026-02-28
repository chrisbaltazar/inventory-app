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
use App\Service\Message\Producer\LoanReturnReminderMessageProducer;
use DateTimeImmutable;
use Tests\AbstractKernelTestCase;

class LoanReturnReminderMessageProducerTest extends AbstractKernelTestCase
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

        $eventFuture = EventFactory::create(returnDate: new DateTimeImmutable('+2 days'));
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

        $eventComing = EventFactory::create(returnDate: new DateTimeImmutable('+1 day'));
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

        /** @var LoanReturnReminderMessageProducer $test */
        $test = $this->get(LoanReturnReminderMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_RETURN_REMINDER->value,
            'user' => $userOnEventComing1,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_RETURN_REMINDER->value,
            'user' => $userOnEventComing2,
        ]);

        // Re-run
        $test->produce();
        $this->assertDatabaseCount(2, Message::class);
    }

    public function testMessagesValidation(): void
    {
        /** @var LoanReturnReminderMessageProducer $test */
        $test = $this->get(LoanReturnReminderMessageProducer::class);

        $message1 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_REMINDER,
            scheduledAt: (new DateTimeImmutable('today'))->setTime(9, 0)
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertTrue($test->isRelevant($message1));

        $message2 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_REMINDER,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::SENT->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertTrue($test->isRelevant($message2));

        $message3 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_REMINDER,
            scheduledAt: new DateTimeImmutable('-1 day'),
        )
            ->setStatus(null)
            ->setProcessedAt(null);

        $this->assertFalse($test->isRelevant($message3));

        $message4 = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_REMINDER,
            scheduledAt: new DateTimeImmutable('-1 min'),
        )
            ->setStatus(MessageStatusEnum::ERROR->value)
            ->setProcessedAt(new DateTimeImmutable('now'));

        $this->assertTrue($test->isRelevant($message4));
    }
}
