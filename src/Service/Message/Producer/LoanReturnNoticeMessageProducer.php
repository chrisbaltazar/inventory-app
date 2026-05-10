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

class LoanReturnNoticeMessageProducer implements MessageProducerInterface
{
    use TimeDiff;

    const RETURN_RANGE_START = '+5 days';
    const RETURN_RANGE_END = '+8 days';

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly MessageComposer $messageBuilder,
        private readonly MessageRepository $messageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function produce(): void
    {
        $returnDateStart = new \DateTimeImmutable(self::RETURN_RANGE_START);
        $returnDateFinal = new \DateTimeImmutable(self::RETURN_RANGE_END);
        $loanReturnUsers = $this->getLoanUsers($returnDateStart, $returnDateFinal);
        foreach ($loanReturnUsers as $returnDate => $users) {
            /** @var User $user */
            foreach ($users as $user) {
                $existingMessage = $this->existMessage([
                    'user' => $user,
                    'type' => MessageTypeEnum::LOAN_RETURN_NOTICE,
                    'keyword' => $returnDate,
                ]);
                if ($existingMessage) {
                    continue;
                }

                $message = $this->messageBuilder->createLoanReturnNoticeMessage($user, $returnDate);
                $this->entityManager->persist($message);
            }
        }
        $this->entityManager->flush();
    }

    public function existMessage(array $data): ?Message
    {
        return $this->messageRepository->findOneWith(... $data);
    }

    private function getLoanUsers(\DateTimeImmutable $date1, \DateTimeImmutable $date2): array
    {
        $loanUsers = [];
        $allLoans = $this->loanRepository->findAllWithReturnBetween($date1, $date2);
        /** @var Loan $loan */
        foreach ($allLoans as $loan) {
            $user = $loan->getUser();
            $event = $loan->getEvent();
            $returnDate = $event->getReturnDate()->format('d/m/Y');
            $loanUsers[$returnDate][$user->getId()] = $user;
        }

        return $loanUsers;
    }

    public function isRegistered(MessageTypeEnum $messageType): bool
    {
        return $messageType->isLoanReturnNotice();
    }

    public function isExpired(Message $message): bool
    {
        return $this->getDiffHours($message->getScheduledAt()) > 72;
    }
}