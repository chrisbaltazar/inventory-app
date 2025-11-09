<?php

namespace App\Tests\Service;

use App\DataFixtures\Factory\MessageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageManagerServiceTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testSomething(): void
    {
//        $this->assertSame('test', $kernel->getEnvironment());
        $user = UserFactory::create(phoneNumber: '+34234567890');
        $message = MessageFactory::create(user: $user);
        $message->setRecipient(null);

        $this->entityManager->persist($user);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->assertDatabaseCount(1, Message::class);
    }

    protected function assertDatabaseCount(int $count, string $entityClass): void
    {
        $repository = $this->entityManager->getRepository($entityClass);

        $result = $repository->findAll();

        self::assertCount($count, $result);
    }
}
