<?php

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Enum\MessageTypeEnum;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageComposer;
use App\Service\Time\ClockInterface;
use App\Service\Time\TimeDiff;
use Doctrine\ORM\EntityManagerInterface;

class HolidaysMessageProducer implements MessageProducerInterface
{

    use TimeDiff;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageRepository $messageRepository,
        private readonly MessageComposer $messageBuilder,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
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

    public function existMessage(array $data): ?Message
    {
        return $this->messageRepository->findOneWith(... $data);
    }

    private function isXmas(): bool
    {
        return $this->clock->today()->format('m-d') === '12-24';
    }

    private function isNewYearsEve(): bool
    {
        return $this->clock->today()->format('m-d') === '12-31';
    }

    private function createXmasMessages(array $allUsers): void
    {
        foreach ($allUsers as $user) {
            $existingMessage = $this->existMessage([
                'user' => $user,
                'type' => MessageTypeEnum::CHRISTMAS_GREETING,
                'scheduled' => $this->clock->today(),
            ]);
            if ($existingMessage) {
                continue;
            }

            $message = $this->messageBuilder->merryChristmas($user, $this->clock->today()->setTime(18, 0));
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    private function createNewYearMessages(array $allUsers): void
    {
        foreach ($allUsers as $user) {
            $existingMessage = $this->existMessage([
                'user' => $user,
                'type' => MessageTypeEnum::NEW_YEAR_GREETING,
                'scheduled' => $this->clock->today(),
            ]);
            if ($existingMessage) {
                continue;
            }

            $message = $this->messageBuilder->newYearsEve($user, $this->clock->today()->setTime(22, 0));
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function isRegistered(MessageTypeEnum $messageType): bool
    {
        return $messageType->isChristmasGreeting() || $messageType->isNewYearGreeting();
    }

    public function isExpired(Message $message): bool
    {
        return match (MessageTypeEnum::from($message->getType())) {
            MessageTypeEnum::CHRISTMAS_GREETING => $this->getDiffHours($message->getScheduledAt()) > 6,
            MessageTypeEnum::NEW_YEAR_GREETING => $this->getDiffHours($message->getScheduledAt()) > 2,
            default => true
        };
    }
}
