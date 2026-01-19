<?php

namespace App\Service\User;

use App\Entity\Event;
use App\Entity\Loan;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\LoanRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserHomeDataService implements UserHomeDataInterface
{

    public function __construct(
        private EventRepository $eventRepository,
        private LoanRepository $loanRepository,
        private Security $security
    ) {
    }

    public function getData(): array
    {
        $loans = $this->getUserLoans();

        return [
            'events' => $this->getPublicEvents(),
            'loans' => $loans,
            'isDelayed' => $this->isUserDelayed($loans),
        ];
    }

    private function getPublicEvents(): array
    {
        return array_filter($this->eventRepository->findAllOpen(), fn(Event $event) => $event->isPublic());
    }

    private function getUserLoans(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->loanRepository->findOpenByUser($user);
    }

    private function isUserDelayed(array $loans): bool
    {
        $now = new \DateTime('now');
        /** @var Loan $loan */
        foreach ($loans as $loan) {
            if (!$loan->getEndDate() && $loan->getEvent()?->getReturnDate()?->format('U') > $now->format('U')) {
                return true;
            }
        }

        return false;
    }
}