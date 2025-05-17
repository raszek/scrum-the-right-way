<?php

namespace App\Repository\Project;

use App\Entity\Project\ProjectRole;
use App\Enum\Project\ProjectRoleEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectRole>
 */
class ProjectRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectRole::class);
    }

    public function adminRole(): ProjectRole
    {
        return $this->getReference(ProjectRoleEnum::Admin);
    }

    public function developerRole(): ProjectRole
    {
        return $this->getReference(ProjectRoleEnum::Developer);
    }

    public function getReference(ProjectRoleEnum $enum): ProjectRole
    {
        return $this->getEntityManager()
            ->getReference(ProjectRole::class, $enum->value);
    }
}
