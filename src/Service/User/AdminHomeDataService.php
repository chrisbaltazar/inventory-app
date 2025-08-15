<?php

namespace App\Service\User;

use App\Repository\EventRepository;
use App\Repository\LoanRepository;

class AdminHomeDataService implements UserHomeDataInterface
{
    public function __construct(
        private EventRepository $eventRepository,
        private LoanRepository $loanRepository,
    ) {
    }

    public function getData(): array
    {
        return [
            'events' => $this->eventRepository->findAllOpen(),
            'loans' => $this->loanRepository->findAllDelayed(),
        ];
    }
}