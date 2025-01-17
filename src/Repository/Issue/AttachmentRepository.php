<?php

namespace App\Repository\Issue;

use App\Entity\Issue\Attachment;
use App\Entity\Issue\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attachment>
 */
class AttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attachment::class);
    }


    /**
     * @return Attachment[]
     */
    public function issueAttachments(Issue $issue): array
    {
        $queryBuilder = $this->createQueryBuilder('attachment');

        $queryBuilder
            ->addSelect('files')
            ->join('attachment.file', 'files')
            ->where('attachment.issue = :issue')
            ->setParameter('issue', $issue);

        return $queryBuilder->getQuery()->getResult();
    }
}
