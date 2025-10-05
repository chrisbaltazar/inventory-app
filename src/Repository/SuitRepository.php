<?php

namespace App\Repository;

use App\Entity\Suit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Suit>
 *
 * @method Suit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Suit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Suit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suit::class);
    }

    /**
     * @return Suit[]
     */
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('s')
            ->orderBy('s.region', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
