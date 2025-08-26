<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Enum\RegionEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inventory>
 *
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventory::class);
    }

    public function findByRegion(RegionEnum $region)
    {
        return $this->createQueryBuilder('inv')
            ->select('inv', 'it')
            ->join('inv.item', 'it')
            ->where('it.region = :region')
            ->setParameter('region', $region->value)
            ->getQuery()
            ->getResult();
    }
}
