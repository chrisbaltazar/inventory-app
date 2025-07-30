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

    public function getRegionInventory(RegionEnum $region): array
    {
        $inventory = $this->inventoryRepository->findByRegion($region);
        $inventory = array_filter($inventory, fn(Inventory $i) => $i->getQuantity() > 0);
        $items = [];
        /** @var Inventory $inventoryItem */
        foreach ($inventory as $inventoryItem) {
            $items[$inventoryItem->getItem()->getName()][$this->formatKey($inventoryItem)] = $this->formatItem(
                $inventoryItem
            );
        }

        return $items;
    }

    private function formatItem(Inventory $inventory): string
    {
        return trim(vsprintf('Talla: %s %s', [
                $inventory->getSize(),
                $inventory->getColor(),
            ])
        );
    }

    private function formatKey(Inventory $inventory): string
    {
        return sprintf('%s:%s', $inventory->getSize(), $inventory->getColor());
    }
}