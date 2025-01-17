<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Entity\Thread\Thread;
use App\Event\Issue\IssueEventList;
use App\Event\Thread\ThreadEventList;
use App\Factory\Event\EventFactory;
use App\Repository\Issue\IssueRepository;
use App\Repository\Project\ProjectRepository;
use App\Repository\Thread\ThreadRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ThreadRepository $threadRepository,
        private readonly IssueRepository $issueRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $this->loadProjectEvents($project);
            $this->loadIssueEvents($project);
        }
    }

    public function loadIssueEvents(Project $project): void
    {
        $issues = $this->issueRepository->findBy([
            'project' => $project
        ]);

        foreach ($issues as $issue) {
            EventFactory::createOne([
                'name' => IssueEventList::CREATE_ISSUE,
                'project' => $project,
                'createdBy' => $issue->getCreatedBy(),
                'createdAt' => $issue->getCreatedAt(),
                'issue' => $issue,
                'params' => [
                    'issueId' => $issue->getId()
                ]
            ]);
        }
    }

    private function loadProjectEvents(Project $project): void
    {
        $threads = $this->threadRepository->findBy([
            'project' => $project,
        ]);

        foreach ($threads as $thread) {
            $this->loadThreadEvents($thread, $project);
        }
    }

    private function loadThreadEvents(Thread $thread, Project $project): void
    {
        EventFactory::createOne([
            'name' => ThreadEventList::THREAD_CREATE,
            'project' => $project,
            'createdBy' => $thread->getCreatedBy(),
            'createdAt' => $thread->getCreatedAt(),
            'params' => [
                'threadId' => $thread->getId()->integerId(),
            ]
        ]);

        if ($thread->isClosed()) {
            EventFactory::createOne([
                'name' => ThreadEventList::THREAD_CLOSE,
                'project' => $project,
                'createdBy' => $thread->getCreatedBy(),
                'createdAt' => $thread->getCreatedAt(),
                'params' => [
                    'threadId' => $thread->getId()->integerId(),
                ]
            ]);
        }

        $threadMessages = $thread->getThreadMessages()->slice(1);

        foreach ($threadMessages as $threadMessage) {
            EventFactory::createOne([
                'name' => ThreadEventList::THREAD_ADD_MESSAGE,
                'project' => $project,
                'createdBy' => $threadMessage->getCreatedBy(),
                'createdAt' => $threadMessage->getCreatedAt(),
                'params' => [
                    'threadId' => $thread->getId()->integerId(),
                ]
            ]);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ThreadFixtures::class,
            ProjectFixtures::class,
            IssueFixtures::class
        ];
    }
}
