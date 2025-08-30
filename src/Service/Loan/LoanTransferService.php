<?php

namespace App\Service\Loan;

use App\Entity\Event;
use App\Entity\User;
use App\Enum\LoanStatusEnum;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoanTransferService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoanRepository $loanRepository,
    ) {
    }

    public function __invoke(User $user, Event $sourceEvent, Event $targetEvent): void
    {
        if ($sourceEvent->getId() === $targetEvent->getId()) {
            throw new \UnexpectedValueException('Event source and target must be different');
        }

        if ($targetEvent->getDate()->getTimestamp() < $sourceEvent->getDate()->getTimestamp()) {
            throw new \UnexpectedValueException('Target event date must be greater then source event');
        }

        $now = new \DateTimeImmutable();
        if ($targetEvent->getReturnDate() && $targetEvent->getReturnDate()->getTimestamp() > $now->getTimestamp()) {
            throw new \UnexpectedValueException('Target event must be open');
        }

        $transferLoanItems = $this->loanRepository->findOpenByEvent($sourceEvent, $user);
        if (empty($transferLoanItems)) {
            throw new \UnexpectedValueException('There are no more items to transfer from source event');
        }

        foreach ($transferLoanItems as $loanItem) {
            $newLoan = clone $loanItem;
            $newLoan->setEvent($targetEvent);
            $newLoan->setStartDate($now);
            $newLoan->setStatus(LoanStatusEnum::OPEN->value);
            $this->entityManager->persist($newLoan);

            $loanItem->setEndDate($now);
            $loanItem->setStatus(LoanStatusEnum::CLOSED->value);
        }

        $this->entityManager->flush();
    }
}