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

class LoanReturnMessageProducer implements MessageProducerInterface
{
    const RETURN_NOTICE_START = '+5 days';
    const RETURN_NOTICE_END = '+8 days';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageBuilder $messageBuilder,
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $this->produceLoanReturnNotice();
    }

    public function existMessage(...$args): ?Message
    {
        /** @var MessageTypeEnum $type */
        /** @var User $user */
        [$type, $user] = $args;

        return $this->messageRepository->findOneWith(
            type: $type,
            user: $user,
        );
    }

    public function isRelevant(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return $type->isLoanReturnNotice()
            && $message->getStatus() !== MessageStatusEnum::ERROR->value
            && $message->getScheduledAt() >= new \DateTimeImmutable(self::RETURN_NOTICE_START)
            && $message->getScheduledAt() <= new \DateTimeImmutable(self::RETURN_NOTICE_END);
    }

    public function isWaiting(Message $message): bool
    {
        $type = MessageTypeEnum::from($message->getType());

        return $type->isLoanReturnNotice()
            && !$message->getStatus()
            && $message->getScheduledAt()?->format('Ymd') === (new \DateTime('now'))->format('Ymd');
    }

    private function produceLoanReturnNotice(): void
    {
        $returnDateStart = new \DateTimeImmutable(self::RETURN_NOTICE_START);
        $returnDateFinal = new \DateTimeImmutable(self::RETURN_NOTICE_END);
        $users = $this->getLoanUsers($returnDateStart, $returnDateFinal);
        foreach ($users as $user) {
            $existingMessage = $this->existMessage(MessageTypeEnum::LOAN_RETURN_NOTICE, $user);
            if ($existingMessage && $this->isRelevant($existingMessage)) {
                continue;
            }

            $message = $this->messageBuilder->createLoanReturnNoticeMessage($user, $returnDateFinal);
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    /**
     * @return User[]
     */
    private function getLoanUsers(\DateTimeImmutable $date1, \DateTimeImmutable $date2): array
    {
        $loanUsers = [];
        $allLoans = $this->loanRepository->findAllWithLoanReturnBetween($date1, $date2);
        /** @var Loan $loan */
        foreach ($allLoans as $loan) {
            $user = $loan->getUser();
            $loanUsers[$user?->getId()] = $user;
        }

        return array_filter($loanUsers);
    }


}