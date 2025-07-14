<?php

namespace App\Repository\User;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Helper\ArrayHelper;
use App\Repository\QueryBuilder\QueryBuilder;
use App\Table\User\UserRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct($registry, User::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function create(User $user): void
    {
        $this->getEntityManager()->persist($user);

        $this->getEntityManager()->flush();
    }

    /**
     * @param int[] $userIds
     * @return User[]
     */
    public function findInIds(array $userIds): array
    {
        $queryBuilder = $this->createQueryBuilder('user');

        $queryBuilder
            ->where('user.id IN (:userIds)')
            ->setParameter('userIds', $userIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Project $project
     * @param string $name
     * @return User[]
     */
    public function searchNonProjectUsers(Project $project, string $name): array
    {
        $queryBuilder = $this->nonProjectUsersQuery($project);

        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('user.firstName', ':name'),
            $queryBuilder->expr()->like('user.lastName', ':name'),
            $queryBuilder->expr()->like('user.email', ':name'),
        ));

        $queryBuilder->setParameter('name', '%'.$name.'%');

        return $queryBuilder->getQuery()->getResult();
    }

    public function nonProjectUsersQuery(Project $project): QueryBuilder
    {
        $projectUsers = $this->projectUsers($project);

        $userIds = ArrayHelper::map($projectUsers, fn(User $u) => $u->getId());

        $queryBuilder = $this->createQueryBuilder('user');

        $queryBuilder
            ->where('user.id NOT IN (:userIds)')
            ->sqidsParameter('userIds', $userIds);

        return $queryBuilder;
    }

    /**
     * @return User[]
     */
    public function nonProjectUsers(Project $project): array
    {
        return $this->nonProjectUsersQuery($project)->getQuery()->getResult();
    }

    public function projectUsersQuery(Project $project): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('user');

        $queryBuilder
            ->innerJoin('user.projectMembers', 'projectMembers')
            ->innerJoin('projectMembers.project', 'project')
            ->where('project.id = :projectId')
            ->sqidParameter('projectId', $project->getId());

        return $queryBuilder;
    }

    /**
     * @return User[]
     */
    public function projectUsers(Project $project): array
    {
        return $this->projectUsersQuery($project)->getQuery()->getResult();
    }

    /**
     * @param int[] $userIds
     * @return User[]
     */
    public function mappedUsers(array $userIds): array
    {
        $users = $this->findBy([
            'id' => $userIds
        ]);

        return ArrayHelper::indexByCallback($users, fn(User $user) => $user->getId()->integerId());
    }

    public function userNotificationsQuery(): QueryBuilder
    {
        $fetchIdsQuery = $this->createQueryBuilder('user');

        $result = $fetchIdsQuery
            ->select(['user.id'])
            ->join('user.notifications', 'notifications')
            ->where('notifications.isRead = false')
            ->andWhere('notifications.isSentEmail = false')
            ->getQuery()
            ->getArrayResult();

        $ids = array_column($result, 'id');

        $queryBuilder = $this->createQueryBuilder('user');

        $queryBuilder
            ->where('user.id IN (:ids)')
            ->sqidsParameter('ids', $ids);

        return $queryBuilder;
    }

    public function listUserQuery(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('user');

        $queryBuilder
            ->select(sprintf("NEW %s(user.id, user.email, concat(user.firstName, ' ', user.lastName))", UserRecord::class))
            ->addSelect("concat(user.firstName, ' ', user.lastName) as HIDDEN fullName")
            ->orderBy('user.id', 'DESC');

        return $queryBuilder;
    }
}
