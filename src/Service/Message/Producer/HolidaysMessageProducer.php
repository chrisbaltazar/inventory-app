<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;

class HolidaysMessageProducer implements MessageProducerInterface
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageRepository $messageRepository,
        private readonly MessageBuilder $messageBuilder,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $allUsers = $this->userRepository->findAll();

        if ($this->isXmas()) {
            $this->createXmasMessages($allUsers);
        }

        if ($this->isNewYearsEve()) {
            $this->createNewYearMessages($allUsers);
        }
    }

    public function existMessage(...$args): ?Message
    {
        /** @var MessageTypeEnum $messageType */
        /** @var User $user */
        [$messageType, $user] = $args;

        return $this->messageRepository->findOneWith(
            type: $messageType,
            user: $user,
            scheduled: new \DateTime('today'),
        );
    }

    public function isRelevant(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return ($type->isChristmasGreeting())
            && $message->getScheduledAt()?->format('Y-m-d') === (new \DateTime('now'))->format('Y-m-d')
            && $message->getStatus() !== MessageStatusEnum::ERROR->value;
    }

    public function isWaiting(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return ($type->isChristmasGreeting())
            && $message->getScheduledAt()?->format('Y-m-d') === (new \DateTime('now'))->format('Y-m-d')
            && !$message->getStatus();
    }

    private function isXmas(): bool
    {
        return (new \DateTime())->format('m-d') === '12-24';
    }

    private function isNewYearsEve(): bool
    {
        return (new \DateTime())->format('m-d') === '12-31';
    }

    private function createXmasMessages(array $allUsers): void
    {
        foreach ($allUsers as $user) {
            $existingMessage = $this->existMessage(MessageTypeEnum::CHRISTMAS_GREETING, $user);
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->merryChristmas($user);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    private function createNewYearMessages(array $allUsers): void
    {
        foreach ($allUsers as $user) {
            $existingMessage = $this->existMessage(MessageTypeEnum::NEW_YEAR_GREETING, $user);
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->newYearsEve($user);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }
}
