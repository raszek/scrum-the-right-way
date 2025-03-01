<?php

namespace App\Service\Observer;

use App\Entity\Issue\Issue;
use App\Entity\Issue\IssueObserver;
use App\Entity\Project\ProjectMember;
use App\Exception\Observer\CannotAddIssueObserverException;
use App\Exception\Observer\CannotRemoveIssueObserverException;
use App\Service\Common\ClockInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueObserverEditor
{

    public function __construct(
        private Issue $issue,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock
    ) {
    }

    public function addObserverIfNotExists(ProjectMember $member): void
    {
        $issue = $this->issue;

        $existingIssueObserver = $issue->getObservers()
            ->findFirst(fn(int $i, IssueObserver $observer) => $observer->getProjectMember()->getId() === $member->getId());

        if ($existingIssueObserver) {
            return;
        }

        $this->createObserver($member);
    }

    public function addObserver(ProjectMember $member): void
    {
        $issue = $this->issue;

        $existingIssueObserver = $issue->getObservers()
            ->findFirst(fn(int $i, IssueObserver $observer) => $observer->getProjectMember()->getId() === $member->getId());

        if ($existingIssueObserver) {
            throw new CannotAddIssueObserverException(sprintf('Issue observer %s already exist', $member->getEmail()));
        }

        $this->createObserver($member);
    }

    public function removeObserver(ProjectMember $member): void
    {
        $issue = $this->issue;

        $existingIssueObserver = $issue->getObservers()
            ->findFirst(fn(int $i, IssueObserver $observer) => $observer->getProjectMember()->getId() === $member->getId());

        if (!$existingIssueObserver) {
            throw new CannotRemoveIssueObserverException('Issue observer not found');
        }

        $issue->removeObserver($existingIssueObserver);

        $issue->setUpdatedAt($this->clock->now());

        $this->entityManager->remove($existingIssueObserver);

        $this->entityManager->flush();
    }

    private function createObserver(ProjectMember $member): void
    {
        $newIssueObserver = new IssueObserver(
            issue: $this->issue,
            projectMember: $member
        );

        $this->issue->addObserver($newIssueObserver);

        $this->issue->setUpdatedAt($this->clock->now());

        $this->entityManager->persist($newIssueObserver);

        $this->entityManager->flush();
    }

}
