<?php

namespace Tests\Service\Message\Producer;

use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
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
        $user2 = UserFactory::create(birthday: new \DateTime('-1 day'));
        $admin = UserFactory::admin();
        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $test = $this->get(BirthdayMessageProducer::class);
        $test->produce();

        $this->assertDatabaseCount(2, Message::class);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::USER_BIRTHDAY_GREET->value,
            'user' => $user1,
        ]);
        $this->assertDatabaseEntity(Message::class, [
            'type' => MessageTypeEnum::ADMIN_BIRTHDAY_NOTIF->value,
            'user' => $admin,
        ]);
    }
}
