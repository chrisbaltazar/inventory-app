<?php

namespace App\Service\Loan;

use App\Entity\Loan;
use App\Entity\User;
use App\Repository\LoanRepository;

class LoanDataService
{
    public function __construct(
        private LoanRepository $loanRepository,
    ) {
    }

    public function getUserLoansByEvent(User $user): array
    {
        $data = [];
        $loans = $this->loanRepository->findAllByUser($user);
        foreach ($loans as $loan) {
            $data[$loan->getEvent()->getName()]['data'] ??= $this->getEventData($loan);
            $data[$loan->getEvent()->getName()]['rows'][] = $this->getLoanData($loan);
        }

        return $data;
    }

    private function getLoanData(Loan $loan): array
    {
        $info = json_decode($loan->getInfo() ?? '', true);

        return [
            'id' => $loan->getId(),
            'user' => $loan->getUser()->getName(),
            'region' => $loan->getItem()->getRegion(),
            'item' => $loan->getItem()->getName(),
            'info' => implode(', ', $info),
            'quantity' => $loan->getQuantity(),
            'startDate' => $loan->getStartDate(),
            'endDate' => $loan->getEndDate(),
            'comments' => $loan->getComments(),
            'status' => $loan->getStatus(),
            'isOpen' => empty($loan->getEndDate()),
        ];
    }

    private function getEventData(Loan $loan): array
    {
        return [
            'id' => $loan->getEvent()->getId(),
            'name' => $loan->getEvent()->getName(),
            'date' => $loan->getEvent()->getDate(),
            'returnDate' => $loan->getEvent()->getReturnDate(),
        ];
    }
}