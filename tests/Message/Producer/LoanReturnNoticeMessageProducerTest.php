<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\ItemFactory;
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

    public function testMessageErrorFound(): void
    {
        $returnDate = new DateTimeImmutable('+7 days');
        $event = EventFactory::create(returnDate: $returnDate);
        $user = UserFactory::create();
        $item = ItemFactory::create();
        $loan = LoanFactory::create(
            startDate: new DateTimeImmutable('now'),
            endDate: null,
            user: $user,
            event: $event,
            item: $item,
        );
        $message = MessageFactory::create(
            type: MessageTypeEnum::LOAN_RETURN_NOTICE,
            user: $user,
            keyword: $returnDate->format('d/m/Y'),
            status: MessageStatusEnum::SENT,
            scheduledAt: new DateTimeImmutable('-1 day'),
            processedAt: new DateTimeImmutable('-1 day'),
        );

        $this->persistAll($event, $user, $item, $loan, $message);

        /** @var LoanReturnNoticeMessageProducer $test */
        $test = $this->get(LoanReturnNoticeMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(1, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_RETURN_NOTICE->value,
            'user' => $user,
        ]);

        $test->produce();
        $this->assertDatabaseCount(1, Message::class);
    }
}
