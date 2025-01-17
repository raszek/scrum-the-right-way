<?php

namespace App\Repository\Issue;

use App\Entity\Issue\IssueType;
use App\Enum\Issue\IssueTypeEnum;
use App\Helper\ArrayHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IssueType>
 */
class IssueTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssueType::class);
    }

    public function issueType(): IssueType
    {
        return $this->findOneBy([
            'id' => IssueTypeEnum::Issue->value
        ]);
    }

    /**
     * @return IssueType[]
     */
    public function fetchCreateTypes(): array
    {
        $createTypeIds = ArrayHelper::map(IssueTypeEnum::createTypes(), fn(IssueTypeEnum $type) => $type->value);

        $queryBuilder = $this->createQueryBuilder('issueType');

        $queryBuilder->where('issueType.id in (:ids)');
        $queryBuilder->setParameter('ids', $createTypeIds);

        return $queryBuilder->getQuery()->getResult();
    }
}
