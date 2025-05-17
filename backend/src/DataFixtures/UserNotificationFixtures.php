<?php

namespace App\DataFixtures;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Factory\User\UserNotificationFactory;
use App\Repository\Event\EventRepository;
use App\Repository\Project\ProjectRepository;
use App\Repository\User\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;

class UserNotificationFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly EventRepository $eventRepository
    ) {
        $this->faker = FakerFactory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $this->loadUserNotifications($user);
        }
    }

    private function loadUserNotifications(User $user): void
    {
        $userProjects = $this->projectRepository->projectList($user);
        foreach ($userProjects as $project) {
            $this->loadProjectNotifications($project, $user);
        }
    }

    private function loadProjectNotifications(Project $project, User $user): void
    {
        $queryBuilder = $this->eventRepository->createQueryBuilder('event');

        $queryBuilder
            ->where('event.project = :project')
            ->andWhere('event.createdBy = :user')
            ->andWhere('event.issue is not null')
            ->sqidParameter('project', $project->getId())
            ->setParameter('user', $user);

        $events = $queryBuilder->getQuery()->getResult();

        foreach ($events as $event) {
            UserNotificationFactory::createOne([
                'event' => $event,
                'forUser' => $user,
                'sentEmail' => true,
                'read' => $this->faker->boolean()
            ]);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
}
