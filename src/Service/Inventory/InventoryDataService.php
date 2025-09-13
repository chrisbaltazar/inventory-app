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
            $gender = $inventoryItem->getItem()->getGender();
            $key = $this->formatKey($inventoryItem);
            $description = $this->formatItem($inventoryItem);
            $items[$gender][$name]['name'] = $name;
            $items[$gender][$name]['gender'] = $gender;
            $items[$gender][$name]['values'][$key] = $description;
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
        $data = [];
        $genderOrder = [GenderEnum::W->name, GenderEnum::M->name, GenderEnum::U->name];
        foreach ($genderOrder as $gender) {
            $genderItems = array_map(function ($item) use ($gender) {
                $item['genderName'] = GenderEnum::fromName($gender)->value;

                return [$item];
            }, $items[$gender] ?? []);

            $data = array_merge($data, ...array_values($genderItems));
        }

        return $data;
//        return array_map(function (array $data) {
//            $data['genderName'] = GenderEnum::fromName($data['gender'])->value;
//
//            return $data;
//        },
//            array_merge(
//                $items[GenderEnum::W->name] ?? [],
//                $items[GenderEnum::M->name] ?? [],
//                $items[GenderEnum::U->name] ?? []
//            ));
    }
}