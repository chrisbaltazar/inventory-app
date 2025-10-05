<?php

namespace App\Repository;

use App\Entity\Item;
use App\Enum\GenderEnum;
use App\Enum\RegionEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('i')
            ->orderBy('i.region', 'ASC')
            ->addOrderBy('i.gender', 'ASC')
            ->addOrderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRegion(RegionEnum $region)
    {
        return $this
            ->createQueryBuilder('it')
            ->where('it.region = :region')
            ->setParameter('region', $region->value)
            ->orderBy('it.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByGender(GenderEnum $gender)
    {
        return $this
            ->createQueryBuilder('i')
            ->where('i.gender = :gender')
            ->orWhere('i.gender = :unisex')
            ->setParameter('gender', $gender->name)
            ->setParameter('unisex', GenderEnum::U->name)
            ->orderBy('i.region', 'ASC')
            ->addOrderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
