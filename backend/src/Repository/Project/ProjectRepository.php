<?php

namespace App\Repository\Project;

use App\Entity\Project\Project;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param User $user
     * @return Project[]
     */
    public function projectList(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('project');

        return $queryBuilder
            ->innerJoin('project.members', 'projectMembers')
            ->where('projectMembers.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

}
