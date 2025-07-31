<?php

namespace App\Service\Loan;

use App\Entity\Event;
use App\Entity\Inventory;
use App\Entity\Loan;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ItemRepository;
use App\Repository\LoanRepository;
use App\Repository\UserRepository;

class LoanDataProcessor
{
    public function __construct(
        private LoanRepository $loanRepository,
        private ItemRepository $itemRepository,
        private UserRepository $userRepository,
        private EventRepository $eventRepository,
    ) {
    }

    public function __invoke(array $data)
    {
        [$user, $event, $items] = $this->validateData($data);
        $this->checkOpenLoanFor($user, $items);
        $this->verifyItemsExistence($items);
        $this->persistItems($items, $user, $event);
    }


    private function validateData(array $data): array
    {
        $userId = $data['user'] ?? null;
        $eventId = $data['event'] ?? null;
        $items = array_filter($data['item'] ?? []);

        if (empty($userId) || empty($eventId)) {
            throw new \UnexpectedValueException('User and Event are required');
        }

        if (empty($items)) {
            throw new \UnexpectedValueException('No items found to be saved');
        }

        foreach ($items as $item) {
            if (count(explode('|', $item)) !== 3) {
                throw new \UnexpectedValueException('Invalid item format: ' . $item);
            }
        }

        return [
            $this->userRepository->find($userId),
            $this->eventRepository->find($eventId),
            $items
        ];
    }

    private function checkOpenLoanFor(User $user, array $items): void
    {
        foreach ($items as $item) {
            $itemId = $this->getItemId($item);
            $loan = $this->loanRepository->findOpenByUserAndItem($user->getId(), $itemId);
            if ($loan) {
                throw new \UnexpectedValueException('User with open loan for: ' . $loan->getItem()->getName());
            }
        }
    }

    private function verifyItemsExistence(array $items): void
    {
        foreach ($items as $item) {
            $itemId = $this->getItemId($item);
            $item = $this->itemRepository->find($itemId);
//            dump($item);
            if (!$item) {
                throw new \UnexpectedValueException('Item not found: ' . $itemId);
            }

            $loans = $this->loanRepository->findOpenByItem($item);
//            dump($loans);
            $totalLoans = array_reduce($loans, fn($carry, Loan $loan) => $carry + $loan->getQuantity(), 0);
            $totalInventory = array_reduce(
                $item->getInventory()->toArray(),
                fn($carry, Inventory $inv) => $carry + $inv->getQuantity(),
                0
            );
//            dd($totalLoans, $totalInventory);
            if (++$totalLoans > $totalInventory) {
                throw new \UnexpectedValueException('Not enough availability for: ' . $item->getName());
            }
        }
    }

    private function getItemId(string $item): int
    {
        return current(explode('|', $item));
    }

    private function persistItems(array $items, User $user, Event $event): void
    {
        $loan = new Loan();
        $loan->setUser($user);
        $loan->setEvent($event);
        foreach ($items as $item) {

        }
    }
}