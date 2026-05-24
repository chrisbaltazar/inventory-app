<?php

declare(strict_types=1);

namespace Message\Producer;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\ItemFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use App\Enum\MessageTypeEnum;
use App\Service\Message\Producer\LoanDelayedReminedProducer;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractKernelTestCase;

class LoanDelayedReminedProducerTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    #[Test]
    public function produce_messages(): void
    {
        $item = ItemFactory::create();
        $userDelayed1 = UserFactory::create();
        $userDelayed2 = UserFactory::create();
        $userDelayed3 = UserFactory::create();
        $eventRecentClosed = EventFactory::create(returnDate: new \DateTimeImmutable('-1 day'));
        $eventLongClosed = EventFactory::create(returnDate: new \DateTimeImmutable('-2 days'));
        $loanOpen1 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: null,
            user: $userDelayed1,
            event: $eventRecentClosed,
            item: $item,
        );
        $loanOpen2 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: null,
            user: $userDelayed2,
            event: $eventRecentClosed,
            item: $item,
        );
        $loanOpen3 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: null,
            user: $userDelayed3,
            event: $eventLongClosed,
            item: $item,
        );

        $userCorrect = UserFactory::create();
        $loanEnded = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: new \DateTimeImmutable('-1 day'),
            user: $userCorrect,
            event: $eventRecentClosed,
            item: $item,
        );

        $existingMessage = MessageFactory::create(
            type: MessageTypeEnum::LOAN_DELAYED_REMINDER,
            user: $userDelayed1,
            scheduledAt: new \DateTimeImmutable('-5 days'),
        );

        $this->persistAll(
            $item,
            $userCorrect,
            $userDelayed1,
            $userDelayed2,
            $userDelayed3,
            $eventRecentClosed,
            $eventLongClosed,
            $loanOpen1,
            $loanOpen2,
            $loanOpen3,
            $loanEnded,
            $existingMessage,
        );

        /** @var LoanDelayedReminedProducer $test */
        $test = $this->get(LoanDelayedReminedProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_DELAYED_REMINDER->value,
            'user' => $userDelayed1, // pre-existing
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_DELAYED_REMINDER->value,
            'user' => $userDelayed3, // new
        ]);

        // Re-run
        $test->produce();
        $this->assertDatabaseCount(2, Message::class);
    }

    #[Test]
    public function message_expiration(): void
    {
        /** @var LoanDelayedReminedProducer $test */
        $test = $this->get(LoanDelayedReminedProducer::class);

        $message = MessageFactory::create(
            type: MessageTypeEnum::LOAN_DELAYED_REMINDER,
            scheduledAt: new \DateTimeImmutable('-1 days'),
        );

        $this->assertFalse($test->isExpired($message));

        $message = MessageFactory::create(
            type: MessageTypeEnum::LOAN_DELAYED_REMINDER,
            scheduledAt: new \DateTimeImmutable('-5 days'),
        );

        $this->assertFalse($test->isExpired($message));

        $message = MessageFactory::create(
            type: MessageTypeEnum::LOAN_DELAYED_REMINDER,
            scheduledAt: new \DateTimeImmutable('-7 days'),
        );

        $this->assertTrue($test->isExpired($message));
    }
}