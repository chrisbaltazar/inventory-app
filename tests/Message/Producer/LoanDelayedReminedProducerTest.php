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
        $eventClosed = EventFactory::create(returnDate: new \DateTimeImmutable('-3 days'));
        $loanOpen1 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: null,
            user: $userDelayed1,
            event: $eventClosed,
            item: $item,
        );
        $loanOpen2 = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: null,
            user: $userDelayed2,
            event: $eventClosed,
            item: $item,
        );

        $userCorrect = UserFactory::create();
        $loanEnded = LoanFactory::create(
            startDate: new \DateTimeImmutable('-7 days'),
            endDate: new \DateTimeImmutable('-3 days'),
            user: $userCorrect,
            event: $eventClosed,
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
            $eventClosed,
            $loanOpen1,
            $loanOpen2,
            $loanEnded,
            $existingMessage,
        );

        /** @var LoanDelayedReminedProducer $test */
        $test = $this->get(LoanDelayedReminedProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_DELAYED_REMINDER->value,
            'user' => $userDelayed1,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::LOAN_DELAYED_REMINDER->value,
            'user' => $userDelayed2,
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