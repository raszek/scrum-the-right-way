<?php

namespace App\Repository\User;

use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Enum\User\UserCodeTypeEnum;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCode>
 */
class UserCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCode::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return new QueryBuilder($this->getEntityManager())
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function findLatestCode(
        string $activationCode,
        UserCodeTypeEnum $type,
        ?User $user
    ): ?UserCode
    {
        if (!$user) {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('userCode');

        $queryBuilder
            ->where('userCode.code = :activationCode')
            ->setParameter('activationCode', $activationCode)
            ->andWhere('userCode.mainUser = :user')
            ->sqidParameter('user', $user->getId())
            ->andWhere('userCode.type = :type')
            ->setParameter('type', $type->value)
            ->orderBy('userCode.id', 'DESC');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function create(UserCode $userCode): void
    {
        $this->getEntityManager()->persist($userCode);

        $this->getEntityManager()->flush();
    }
}
