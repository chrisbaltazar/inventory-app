<?php

namespace App\Service\Inventory;

use App\Entity\Inventory;
use App\Enum\GenderEnum;
use App\Enum\RegionEnum;
use App\Repository\InventoryRepository;

class InventoryDataService
{
    public function __construct(private InventoryRepository $inventoryRepository)
    {
    }

    public function __invoke(RegionEnum $region): array
    {
        $items = [];
        $inventory = $this->inventoryRepository->findByRegion($region);
        /** @var Inventory $inventoryItem */
        foreach ($inventory as $inventoryItem) {
            if ($inventoryItem->getQuantity() <= 0) {
                continue;
            }

            $name = $inventoryItem->getItem()->getName();
            $key = $this->formatKey($inventoryItem);
            $description = $this->formatItem($inventoryItem);
            $items[$name]['values'][$key] = $description;
            $items[$name]['gender'] = $inventoryItem->getItem()->getGender();
        }

        return $this->sortItemsByGender($items);
    }

    private function formatItem(Inventory $inventory): string
    {
        return trim(vsprintf('Talla: %s %s', [
                $inventory->getSize(),
                $inventory->getDescription(),
            ])
        );
    }

    private function formatKey(Inventory $inventory): string
    {
        $data = array_merge(['id' => $inventory->getId()], $inventory->getInfo());

        return trim(implode('|', $data), '|');
    }

    private function sortItemsByGender(array $items): array
    {
        $items = array_map(function (array $itemData) {
            $gender = GenderEnum::fromName($itemData['gender']);

            $itemData['genderName'] = $gender->value;
            $itemData['sorting'] = match (true) {
                $gender->isFemale() => 1,
                $gender->isMale() => 2,
                default => 3,
            };

            return $itemData;
        }, $items);

        uasort($items, fn(array $a, array $b) => $a['sorting'] <=> $b['sorting']);

        return $items;
    }
}