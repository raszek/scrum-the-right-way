<?php

namespace App\Repository\Project;

use App\Entity\Project\ProjectMemberRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMemberRole>
 */
class ProjectMemberRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMemberRole::class);
    }

    /**
     * @param int[] $memberRoleIds
     * @return ProjectMemberRole[]
     */
    public function findInIds(array $memberRoleIds): array
    {
        $queryBuilder = $this->createQueryBuilder('projectMemberRole');

        $queryBuilder
            ->addSelect('projectMember')
            ->innerJoin('projectMemberRole.projectMember', 'projectMember')
            ->where('projectMemberRole.id IN (:ids)');


        $queryBuilder->setParameter('ids', $memberRoleIds);

        return $queryBuilder->getQuery()->getResult();
    }
}
