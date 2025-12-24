<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
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
    }

    public function testMessagesValidation(): void {}
}
