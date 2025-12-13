<?php

namespace App\Service\Message\Producer;

use App\Entity\Loan;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Repository\LoanRepository;
use App\Repository\MessageRepository;
use App\Service\Message\MessageBuilder;
use Doctrine\ORM\EntityManagerInterface;

class LoanReturnReminderMessageProducer implements MessageProducerInterface
{
    const RETURN_RANGE_START = 'now';
    const RETURN_RANGE_END = '+1 day';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageBuilder $messageBuilder,
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $returnRangeStart = new \DateTimeImmutable(self::RETURN_RANGE_START);
        $returnRangeFinal = new \DateTimeImmutable(self::RETURN_RANGE_END);
        $loanReturnUsers = $this->getLoanUsers($returnRangeStart, $returnRangeFinal);
        foreach ($loanReturnUsers as $returnDate => $users) {
            /** @var User $user */
            foreach ($users as $user) {
                $existingMessage = $this->existMessage(MessageTypeEnum::LOAN_RETURN_REMINDER, $user, $returnDate);
                if ($existingMessage && $this->isRelevant($existingMessage)) {
                    continue;
                }

                $message = $this->messageBuilder->createLoanReturnReminderMessage($user, $returnDate);
                $this->entityManager->persist($message);
            }
        }
        $this->entityManager->flush();
    }

    public function existMessage(...$args): ?Message
    {
        /** @var MessageTypeEnum $type */
        /** @var User $user */
        [$type, $user, $returnDate] = $args;

        return $this->messageRepository->findOneWith(
            type: $type,
            user: $user,
        );
    }

    public function isRelevant(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());
        // Scheduled date is not relevant for loan return reminders
        return $type->isLoanReturnReminder()
            && $message->getStatus() !== MessageStatusEnum::ERROR->value
            && (str_contains($message->getContent(), 'hoy!') || str_contains($message->getContent(), 'mañana!'));
    }

    public function isWaiting(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return $type->isLoanReturnReminder()
            && !$message->getStatus()
            && $message->getScheduledAt()?->format('Ymd') === (new \DateTime('now'))->format('Ymd');
    }

    private function getLoanUsers(\DateTimeImmutable $date1, \DateTimeImmutable $date2): array
    {
        $loanUsers = [];
        $allLoans = $this->loanRepository->findAllWithLoanReturnBetween($date1, $date2);
        /** @var Loan $loan */
        foreach ($allLoans as $loan) {
            $user = $loan->getUser();
            $event = $loan->getEvent();
            $returnDate = $event->getReturnDate()->format('d/m/Y');
            $loanUsers[$returnDate][$user->getId()] = $user;
        }

        return $loanUsers;
    }


}