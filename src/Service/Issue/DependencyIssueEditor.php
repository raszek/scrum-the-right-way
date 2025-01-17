<?php

namespace App\Service\Issue;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueDependency;
use App\Exception\Issue\CannotAddIssueDependencyException;
use App\Exception\Issue\CannotRemoveIssueDependencyException;
use Doctrine\ORM\EntityManagerInterface;

readonly class DependencyIssueEditor
{

    public function __construct(
        private Issue $issue,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Issue $dependency
     * @return void
     * @throws CannotAddIssueDependencyException
     */
    public function addDependency(Issue $dependency): void
    {
        $this->guardAgainstInvalidDependency($dependency);

        $newDependency = new IssueDependency(
            issue: $this->issue,
            dependency: $dependency
        );

        $this->issue->addIssueDependency($newDependency);

        $this->entityManager->persist($newDependency);

        $this->entityManager->flush();
    }

    /**
     * @param Issue $dependency
     * @return void
     * @throws CannotRemoveIssueDependencyException
     */
    public function removeDependency(Issue $dependency): void
    {
        $foundDependency = $this->issue->getIssueDependencies()->findFirst(
            fn(int $i, IssueDependency $issueDependency) => $issueDependency->getDependency()->getId() === $dependency->getId()
        );

        if (!$foundDependency) {
            throw new CannotRemoveIssueDependencyException(
                sprintf(
                    'Issue dependency %s not exist',
                    $dependency->getCode()
                )
            );
        }

        $this->issue->removeIssueDependency($foundDependency);

        $this->entityManager->remove($foundDependency);

        $this->entityManager->flush();
    }

    private function guardAgainstInvalidDependency(Issue $dependency): void
    {
        if ($this->issue->getId() === $dependency->getId()) {
            throw new CannotAddIssueDependencyException('Issue cannot add itself as dependency');
        }

        $dependencyAlreadyExist = $this->issue->getIssueDependencies()->findFirst(
            fn(int $i, IssueDependency $issueDependency) => $issueDependency->getDependency()->getId() === $dependency->getId()
        );

        if ($dependencyAlreadyExist) {
            throw new CannotAddIssueDependencyException(
                sprintf(
                    'Cannot add %s as dependency second time',
                    $dependency->getCode()
                )
            );
        }
    }

}
