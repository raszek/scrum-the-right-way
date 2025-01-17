<?php

namespace App\DataFixtures;

use App\Entity\Project\ProjectMember;
use App\Entity\Thread\Thread;
use App\Factory\Thread\ThreadFactory;
use App\Factory\Thread\ThreadMessageFactory;
use App\Repository\Project\ProjectRepository;
use App\Repository\Thread\ThreadStatusRepository;
use App\Service\Common\RandomService;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Random\RandomException;

class ThreadFixtures extends Fixture implements DependentFixtureInterface
{

    private Generator $faker;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly ThreadStatusRepository $threadStatusRepository,
        private readonly RandomService $randomService,
    ) {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $projects = $this->projectRepository->findAll();

        $openStatus = $this->threadStatusRepository->openStatus();
        $closedStatus = $this->threadStatusRepository->closedStatus();

        foreach ($projects as $project) {
            $projectMembers = $project->getMembers()->getValues();

            foreach (range(1, 20) as $i) {
                $randomProjectMember = $this->randomService->randomElement($projectMembers);
                $thread = ThreadFactory::createOne([
                    'createdBy' => $randomProjectMember->getUser(),
                    'project' => $project,
                    'status' => $i > 10 ? $openStatus : $closedStatus,
                ]);

                $this->createThreadMessages($thread, $project->getMembers());
            }
        }
    }

    /**
     * @param Thread $thread
     * @param Collection<ProjectMember> $members
     * @return void
     * @throws RandomException
     */
    private function createThreadMessages(Thread $thread, Collection $members): void
    {
        $messageCount = random_int(1, 6);

        $createdAt = new CarbonImmutable($thread->getCreatedAt());

        foreach (range(1, $messageCount) as $i) {
            if ($i === 1) {
                ThreadMessageFactory::createOne([
                    'thread' => $thread,
                    'createdBy' => $thread->getCreatedBy(),
                    'createdAt' => $thread->getCreatedAt(),
                    'content' => $this->exampleMarkdown(),
                    'number' => $i
                ]);
            } else {
                /**
                 * @var ProjectMember $randomMember
                 */
                $randomMember = $this->randomService->randomElement($members->getValues());

                ThreadMessageFactory::createOne([
                    'thread' => $thread,
                    'createdBy' => $randomMember->getUser(),
                    'createdAt' => $createdAt->addWeeks($i),
                    'number' => $i
                ]);
            }
        }
    }

    private function exampleMarkdown(): string
    {
        return <<<Markdown
        ## Some header

        **Some bold**
        *Some Italic*
        ~~Some strike~~

        ***
        > Some blockqoute

        * Unordered list 1
        * Unordered list 2


        1. Ordered list 1
        2. Ordered list 2

        * [ ] To some task


        | Column 1 | Column 2 |
        | --- | --- |
        | Row 1 | Row 2 |

        [Example link](https://example.ocm)

        `Code block`

        ```
        const a = 1;
        const b = 2;

        console.log(a + b);
        ```

        Markdown;
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            ThreadStatusFixtures::class,
            UserFixtures::class
        ];
    }
}
