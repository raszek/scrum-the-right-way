<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Exception\Issue\NoOrderSpaceException;
use App\Exception\Issue\OutOfBoundPositionException;
use App\Repository\Issue\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SubIssueEditor
{

    public function __construct(
        private Issue $issue,
        private IssueRepository $issueRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param int $position
     * @return void
     * @throws OutOfBoundPositionException
     */
    public function setPosition(int $position): void
    {
        $query = $this->issueRepository->featureIssueQuery($this->issue->getParent());
        $query->andWhere('issue.id <> :issueId');
        $query->setParameter('issueId', $this->issue->getId());

        $isFirstPosition = $position <= 1;
        if ($isFirstPosition) {
            $query->setMaxResults(1);
        } else {
            $query->setFirstResult($position - 2);
            $query->setMaxResults(2);
        }

        $issues = $query->getQuery()->getResult();

        try {
            $order = $this->calculateOrder($issues, $isFirstPosition);
            $this->issue->setIssueOrder($order);
        } catch (NoOrderSpaceException) {
            $this->issueRepository->reorderFeature($this->issue->getParent());
            $this->setPosition($position);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Issue[] $issues
     * @param bool $isFirstPosition
     * @return int
     * @throws NoOrderSpaceException
     * @throws OutOfBoundPositionException
     */
    public function calculateOrder(array $issues, bool $isFirstPosition): int
    {
        if (count($issues) === 0) {
            throw new OutOfBoundPositionException('Position number is bigger than issue count in the column');
        }

        if (count($issues) === 1) {
            if ($isFirstPosition) {
                return IssueOrderCalculator::findOrderBetween(0, $issues[0]->getIssueOrder());
            } else {
                return $issues[0]->getIssueOrder() + Issue::DEFAULT_ORDER_SPACE;
            }
        }

        return IssueOrderCalculator::findOrderBetween($issues[0]->getIssueOrder(), $issues[1]->getIssueOrder());
    }
}
