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
    const RETURN_DATE_TARGET = '+1 day';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageBuilder $messageBuilder,
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $returnDate = new \DateTimeImmutable(self::RETURN_DATE_TARGET);
        $loanReturnUsers = $this->getLoanUsers($returnDate);
        /** @var User $user */
        foreach ($loanReturnUsers as $user) {
            $existingMessage = $this->existMessage(MessageTypeEnum::LOAN_RETURN_REMINDER, $user, $returnDate);
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->createLoanReturnReminderMessage($user, $returnDate);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function existMessage(...$args): ?Message
    {
        /** @var MessageTypeEnum $type */
        /** @var User $user */
        [$type, $user] = $args;

        return $this->messageRepository->findOneWith(
            type: $type,
            user: $user,
            scheduled: new \DateTimeImmutable(),
        );
    }

    public function isRelevant(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return $type->isLoanReturnReminder()
            && $message->getScheduledAt()?->format('Ymd') === (new \DateTime('now'))->format('Ymd');
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


}