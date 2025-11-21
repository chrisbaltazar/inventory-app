<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[]
     */
    public function findAllPending(): array
    {
        return $this
            ->createQueryBuilder('m')
            ->where('m.processedAt IS NULL')
            ->andWhere('m.status IS NULL')
            ->andWhere('m.scheduledAt <= :now')
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('m.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneWith(
        MessageTypeEnum $type,
        User $user = null,
        \DateTime $scheduled = null,
        string $content = null,
        MessageStatusEnum $status = null,
    ): ?Message {
        $query = $this
            ->createQueryBuilder('m')
            ->where('m.type = :type')
            ->setParameter('type', $type->value);
        if ($scheduled) {
            $query
                ->andWhere('DATE(m.scheduledAt) = :scheduledDate')
                ->setParameter('scheduledDate', $scheduled->format('Y-m-d'));
        }
        if ($user) {
            $query
                ->andWhere('m.user = :user')
                ->setParameter('user', $user);
        }
        if ($content) {
            $query
                ->andWhere('m.content LIKE :content')
                ->setParameter('content', "%$content%");
        }
        if ($status) {
            $query
                ->andWhere('m.status = :status')
                ->setParameter('status', $status->value);
        }

        return $query
            ->orderBy('m.scheduledAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
