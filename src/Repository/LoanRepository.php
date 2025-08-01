<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Loan;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 *
 * @method Loan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loan[]    findAll()
 * @method Loan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function findOpenByUserAndItem(User $user, Item $item): ?Loan
    {
        return $this->createQueryBuilder('l')
            ->where('l.endDate IS NULL')
            ->andWhere('l.user = :user')
            ->andWhere('l.item = :item')
            ->setParameter('user', $user)
            ->setParameter('item', $item)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOpenByItem(int|Item $item): array
    {
        is_int($item) || $item = $item->getId();

        return $this->createQueryBuilder('l')
            ->join('l.item', 'i')
            ->where('l.endDate IS NULL')
            ->andWhere('i.id = :itemId')
            ->setParameter('itemId', $item)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Loan[] Returns an array of Loan objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Loan
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
