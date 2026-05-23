<?php

declare(strict_types=1);

namespace App\Service\Message\Producer;

use App\Entity\Message;
use App\Enum\MessageTypeEnum;
use App\Repository\LoanRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageComposer;
use Doctrine\ORM\EntityManagerInterface;

class LoanDelayedReminedProducer implements MessageProducerInterface
{
    const DELAYED_REMINDER_RANGE = '-7 days';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageRepository $messageRepository,
        private readonly UserRepository $userRepository,
        private readonly MessageComposer $messageComposer,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $allDelayedLoans = $this->loanRepository->findAllDelayedWithCount();
        foreach ($allDelayedLoans as $loan) {
            $user = $this->userRepository->find($loan['userId']);
            $existingMessage = $this->existMessage([
                'user' => $user,
                'type' => MessageTypeEnum::LOAN_DELAYED_REMINDER,
                'date' => new \DateTimeImmutable(self::DELAYED_REMINDER_RANGE),
            ]);

            if ($existingMessage) {
                continue;
            }

            $message = $this->messageComposer->createLoanDelayedReminderMessage($user, $loan['loans']);
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();
    }

    public function existMessage(array $data): ?Message
    {
        return $this->messageRepository->findLastSince(...$data);
    }

    public function isRegistered(MessageTypeEnum $messageType): bool
    {
        return $messageType->isLoanDelayedReminder();
    }

    public function isExpired(Message $message): bool
    {
        return $message->getScheduledAt() < new \DateTimeImmutable(self::DELAYED_REMINDER_RANGE);
    }
}