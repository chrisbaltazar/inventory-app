<?php

namespace App\Service\Loan;

use App\Entity\Event;
use App\Entity\Item;
use App\Entity\Loan;
use App\Entity\User;
use App\Enum\LoanStatusEnum;
use App\Repository\EventRepository;
use App\Repository\InventoryRepository;
use App\Repository\LoanRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoanProcessor
{
    public function __construct(
        private LoanRepository $loanRepository,
        private InventoryRepository $inventoryRepository,
        private UserRepository $userRepository,
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(array $data)
    {
        [$user, $event, $items] = $this->validateData($data);
        $validItems = $this->verifyItemsExistence($items);
        $this->persistItems($validItems, $user, $event);
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
            if (count(explode('|', $item)) < 2) {
                throw new \UnexpectedValueException('Invalid item format: ' . $item);
            }
        }

        return [
            $this->userRepository->find($userId),
            $this->eventRepository->find($eventId),
            $items,
        ];
    }

    private function checkOpenLoanFor(User $user, array $items): void
    {
        foreach ($items as $item) {
            /** @var Item $item */
            $item = $item['item'];
            $loan = $this->loanRepository->findOpenByUserAndItem($user, $item);
            if ($loan) {
                throw new \UnexpectedValueException('User with open loan for: ' . $item->getName());
            }
        }
    }

    private function verifyItemsExistence(array $items): array
    {
        return array_map(function ($itemData) {
            $id = $this->getInventoryId($itemData);
            $inventory = $this->inventoryRepository->find($id);
            $item = $inventory?->getItem();
            if (!$inventory || !$item) {
                throw new \UnexpectedValueException('Item not found for: ' . $itemData);
            }

            $info = $inventory->getInfo();
            $loans = $this->loanRepository->findOpenByItem($item);
            $totalLoans = $this->getTotalLoans($loans, $info);
            if (++$totalLoans > $inventory->getQuantity()) {
                throw new \UnexpectedValueException("Not enough availability for: {$item->getName()} ($itemData)");
            }

            return array_merge($info, ['item' => $item]);
        }, $items);
    }

    private function getInventoryId(string $item): int
    {
        return (int) current(explode('|', $item));
    }

    private function persistItems(array $items, User $user, Event $event): void
    {
        foreach ($items as $item) {
            $loan = new Loan();
            $loan->setUser($user);
            $loan->setEvent($event);
            $loan->setItem($item['item']);
            unset($item['item']);
            $info = json_encode($item, JSON_UNESCAPED_UNICODE);
            $loan->setInfo($info);
            $loan->setQuantity(1);
            $loan->setStartDate(new \DateTimeImmutable());
            $loan->setStatus(LoanStatusEnum::OPEN->value);

            $this->entityManager->persist($loan);
        }

        $this->entityManager->flush();
    }

    private function getTotalLoans(array $loans, array $data): int
    {
        return array_reduce($loans, function ($carry, Loan $loan) use ($data) {
            $quantity = 0;
            $info = json_decode($loan->getInfo(), true);
            $search = array_intersect_key($info, $data);
            ksort($data);
            ksort($search);
            if ($data === $search) {
                $quantity = $loan->getQuantity();
            }

            return $carry + $quantity;
        }, 0);
    }
}