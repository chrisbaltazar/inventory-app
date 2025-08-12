<?php

namespace App\Repository;

use App\Entity\Inventory;
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

    /**
     * @return Loan[]
     */
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

    /**
     * @return Loan[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'e', 'i')
            ->join('l.event', 'e')
            ->join('l.item', 'i')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('l.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByItem(Item $item, ?Inventory $invent = null): array
    {
        $query = $this->createQueryBuilder('l')
            ->select('l', 'e', 'u')
            ->join('l.event', 'e')
            ->join('l.user', 'u')
            ->join('l.item', 'i')
            ->where('l.item = :item')
            ->setParameter('item', $item);

        if ($invent) {
            $index = 0;
            foreach ($invent->getInfo() as $key => $value) {
                $infoVar = 'info_' . ++$index;
                $info = sprintf('"%s":"%s"', $key, $value);
                $query->andWhere('l.info LIKE :' . $infoVar);
                $query->setParameter($infoVar, "%$info%");
            }
        }

        return $query->orderBy('e.date', 'DESC')->addOrderBy('l.startDate', 'DESC')
            ->getQuery()->getResult();
    }
}
