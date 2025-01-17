<?php

namespace App\DataFixtures;

use App\Entity\Issue\Issue;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\Project\ProjectTag;
use App\Entity\Thread\ThreadMessage;
use App\Factory\Issue\IssueFactory;
use App\Factory\Issue\IssueObserverFactory;
use App\Factory\Issue\IssueThreadMessageFactory;
use App\Repository\Issue\IssueColumnRepository;
use App\Repository\Issue\IssueTypeRepository;
use App\Repository\Project\ProjectRepository;
use App\Repository\Project\ProjectTagRepository;
use App\Service\Common\RandomService;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ObjectManager;

class IssueFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly RandomService $randomService,
        private readonly IssueColumnRepository $issueColumnRepository,
        private readonly IssueTypeRepository $issueTypeRepository,
        private readonly ProjectTagRepository $projectTagRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $this->loadProjectIssues($project);
        }

        $manager->flush();
    }

    private function loadProjectIssues(Project $project): void
    {
        $projectMembers = $project->getMembers();

        $backlogColumn = $this->issueColumnRepository->backlogColumn();
        $issueType = $this->issueTypeRepository->issueType();
        $projectTags = $this->projectTagRepository->findBy([
            'project' => $project->getId(),
        ]);

        foreach (range(1, 150) as $i) {
            /**
             * @var ProjectMember $randomProjectMember
             */
            $randomProjectMember = $this->randomService->randomElement($projectMembers->getValues());

            $issue = IssueFactory::createOne([
                'createdBy' => $randomProjectMember->getUser(),
                'createdAt' => CarbonImmutable::create(2012, 12, 12, 12, 12)->addWeeks($i),
                'project' => $project,
                'issueColumn' => $backlogColumn,
                'number' => $i,
                'columnOrder' => $i * 1024,
                'type' => $issueType
            ]);

            IssueObserverFactory::createOne([
                'projectMember' => $randomProjectMember,
                'issue' => $issue,
            ]);

            $this->generateRandomThreadMessage($project, $issue);

            $this->addTags($issue, $projectTags);
        }
    }

    private function generateRandomThreadMessage(Project $project, Issue $issue): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $randomNumber = $this->randomService->randomInteger(0, 170);

        $queryBuilder
            ->select('threadMessage')
            ->from(ThreadMessage::class, 'threadMessage')
            ->join('threadMessage.thread', 'thread')
            ->where('thread.project = :project')
            ->setParameter('project', $project->getId()->integerId())
            ->setMaxResults(1)
            ->setFirstResult($randomNumber);

        try {
            $randomThreadMessage = $queryBuilder->getQuery()->getSingleResult();

            IssueThreadMessageFactory::createOne([
                'issue' => $issue,
                'threadMessage' => $randomThreadMessage,
            ]);
        } catch (NoResultException) {
            // do nothing
        }
    }

    /**
     * @param Issue $issue
     * @param ProjectTag[] $projectTags
     * @return void
     */
    private function addTags(Issue $issue, array $projectTags): void
    {
        $randomProjectTags = $this->randomService->randomElements(
            $projectTags,
            $this->randomService->randomInteger(0, 3)
        );

        foreach ($randomProjectTags as $randomProjectTag) {
            $issue->addTag($randomProjectTag);
        }
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            IssueColumnFixtures::class,
            IssueTypeFixtures::class,
            ProjectTagFixtures::class,
            ThreadFixtures::class,
        ];
    }
}
