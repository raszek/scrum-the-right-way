<?php

namespace App\Repository\User;

use App\Entity\User\User;
use App\Entity\User\UserNotification;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserNotification>
 */
class UserNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserNotification::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function latestNotifications(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('userNotification');

        $queryBuilder
            ->addSelect('event')
            ->join('userNotification.event', 'event')
            ->where('userNotification.forUser = :user')
            ->andWhere('userNotification.isRead = false')
            ->sqidParameter('user', $user->getId())
            ->orderBy('userNotification.id', 'DESC')
            ->setMaxResults(3);

        return $queryBuilder->getQuery()->getResult();
    }

    public function userNotificationQuery(User $user): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('userNotification');

        $queryBuilder
            ->addSelect('event')
            ->join('userNotification.event', 'event')
            ->where('userNotification.forUser = :user')
            ->sqidParameter('user', $user->getId())
            ->orderBy('userNotification.id', 'DESC');

        return $queryBuilder;
    }

    public function markAllNotificationsRead(User $user): void
    {
        $queryBuilder = $this->createQueryBuilder('userNotification');

        $query = $queryBuilder->update()
            ->set('userNotification.isRead', ':isRead')
            ->where('userNotification.forUser = :user')
            ->sqidParameter('user', $user->getId())
            ->setParameter('isRead', true)
            ->getQuery();

        $query->execute();
    }

    public function unreadNotificationCountForUser(User $user): int
    {
        $queryBuilder = $this->createQueryBuilder('userNotification');

        $queryBuilder
            ->select('count(userNotification.id)')
            ->where('userNotification.forUser = :user')
            ->andWhere('userNotification.isRead = false')
            ->sqidParameter('user', $user->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
