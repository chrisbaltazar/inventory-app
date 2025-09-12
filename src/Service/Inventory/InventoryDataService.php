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
            $gender = $inventoryItem->getItem()->getGender();
            $description = $this->formatItem($inventoryItem);
            $items[$gender][$inventoryItem->getId()]['name'] = $name;
            $items[$gender][$inventoryItem->getId()]['gender'] = $gender;
            $items[$gender][$inventoryItem->getId()]['values'][$key] = $description;
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
        return array_map(function (array $data) {
            $data['genderName'] = GenderEnum::fromName($data['gender'])->value;

            return $data;
        },
            array_merge(
                $items[GenderEnum::W->name] ?? [],
                $items[GenderEnum::M->name] ?? [],
                $items[GenderEnum::U->name] ?? []
            ));
    }
}