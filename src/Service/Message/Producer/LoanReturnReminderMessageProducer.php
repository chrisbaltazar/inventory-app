<?php

namespace App\Service\Message\Producer;

use App\Entity\Loan;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageTypeEnum;
use App\Repository\LoanRepository;
use App\Repository\MessageRepository;
use App\Service\Message\MessageComposer;
use App\Service\Time\TimeDiff;
use Doctrine\ORM\EntityManagerInterface;

class LoanReturnReminderMessageProducer implements MessageProducerInterface
{
    use TimeDiff;

    const RETURN_DATE_TARGET = '+1 day';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageComposer $messageBuilder,
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $returnDate = new \DateTimeImmutable(self::RETURN_DATE_TARGET);
        $loanReturnUsers = $this->getLoanUsers($returnDate);
        /** @var User $user */
        foreach ($loanReturnUsers as $user) {
            $existingMessage = $this->existMessage([
                'user' => $user,
                'type' => MessageTypeEnum::LOAN_RETURN_REMINDER,
                'scheduled' => new \DateTimeImmutable(),
            ]);
            if ($existingMessage) {
                continue;
            }

            $message = $this->messageBuilder->createLoanReturnReminderMessage($user, $returnDate);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function existMessage(array $data): ?Message
    {
        return $this->messageRepository->findOneWith(... $data);
    }

    private function getLoanUsers(\DateTimeImmutable $date): array
    {
        $loanUsers = [];
        $allLoans = $this->loanRepository->findAllWithReturnIn($date);
        /** @var Loan $loan */
        foreach ($allLoans as $loan) {
            $user = $loan->getUser();
            $loanUsers[$user->getId()] = $user;
        }

        return $loanUsers;
    }


    public function isRegistered(MessageTypeEnum $messageType): bool
    {
        return $messageType->isLoanReturnReminder();
    }

    public function isExpired(Message $message): bool
    {
        return true;
    }
}