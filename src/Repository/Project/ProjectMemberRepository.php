<?php

namespace App\Repository\Project;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Form\Project\ProjectMemberSearchForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\QueryBuilder\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMember>
 */
class ProjectMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMember::class);
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return (new QueryBuilder($this->getEntityManager()))
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
    }

    public function findById(string $id, Project $project): ?ProjectMember
    {
        return $this->findOneBy([
            'id' => $id,
            'project' => $project,
        ]);
    }

    /**
     * @param Issue $issue
     * @return ProjectMember[]
     */
    public function issueAssignees(Issue $issue): array
    {
        $queryBuilder = $this->createQueryBuilder('projectMember');

        $queryBuilder
            ->innerJoin('projectMember.roles', 'projectMemberRoles')
            ->where('projectMember.project = :project')
            ->sqidParameter('project', $issue->getProject()->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    public function projectMembersQuery(Project $project, ?ProjectMemberSearchForm $searchForm = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('projectMember');

        $queryBuilder
            ->addSelect('projectMemberRoles')
            ->addSelect('user')
            ->innerJoin('projectMember.user', 'user')
            ->leftJoin('projectMember.roles', 'projectMemberRoles')
            ->where('projectMember.project = :projectId')
            ->sqidParameter('projectId', $project->getId());

        if ($searchForm && $searchForm->name) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('user.firstName', ':name'),
                $queryBuilder->expr()->like('user.lastName', ':name'),
                $queryBuilder->expr()->like('user.email', ':name'),
            ));

            $queryBuilder->setParameter('name', '%' . $searchForm->name . '%');
        }

        return $queryBuilder;
    }

    /**
     * @param int[] $memberIds
     * @return ProjectMember[]
     */
    public function findInIds(array $memberIds): array
    {
        $queryBuilder = $this->createQueryBuilder('projectMember');

        $queryBuilder
            ->where('projectMember.id in (:ids)');

        $queryBuilder->setParameter('ids', $memberIds);

        return $queryBuilder->getQuery()->getResult();
    }

}
