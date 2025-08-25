<?php

namespace App\Service\Inventory;

use App\Entity\Inventory;
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
        $inventory = array_filter($inventory, fn(Inventory $i) => $i->getQuantity() > 0);
        /** @var Inventory $inventoryItem */
        foreach ($inventory as $inventoryItem) {
            $name = $this->getItemName($inventoryItem);
            $key = $this->formatKey($inventoryItem);
            $description = $this->formatItem($inventoryItem);
            $items[$name][$key] = $description;
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

    private function getItemName(Inventory $inventoryItem): string
    {
        return sprintf('%s|%s', $inventoryItem->getItem()->getName(), $inventoryItem->getItem()->getGenderName());
    }

    private function sortItemsByGender(array $items):array
    {

    }
}