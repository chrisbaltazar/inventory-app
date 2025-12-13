<?php

namespace App\Service\Message\Producer;

use App\Entity\Loan;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\LoanRepository;

class LoanReturnMessageProducer implements MessageProducerInterface
{
    public function __construct(
        private readonly LoanRepository $loanRepository,
    ) {}

    public function produce(): void
    {
        $this->produceLoanReturnMessages();
    }

    public function existMessage(...$args): ?Message
    {
        // TODO: Implement existMessage() method.
    }

    public function isRelevant(Message $message): bool
    {
        // TODO: Implement isRelevant() method.
    }

    public function isWaiting(Message $message): bool
    {
        // TODO: Implement isWaiting() method.
    }

    private function produceLoanReturnMessages(): void
    {
        $date1 = new \DateTimeImmutable('+5 days');
        $date2 = new \DateTimeImmutable('+7 days');
        $users = $this->getLoanUsers($date1, $date2);

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