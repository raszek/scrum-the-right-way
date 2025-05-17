<?php

namespace App\Service\Tag;

use App\Entity\Issue\Issue;
use App\Entity\Project\ProjectTag;
use App\Event\Issue\Event\SetIssueTagsEvent;
use App\Repository\Project\ProjectTagRepository;
use App\Service\Event\EventPersister;
use Doctrine\ORM\EntityManagerInterface;

readonly class IssueTagEditor
{

    public function __construct(
        private Issue $issue,
        private ProjectTagRepository $projectTagRepository,
        private EntityManagerInterface $entityManager,
        private EventPersister $eventPersister
    ) {
    }

    /**
     * @param string[] $tags
     * @return void
     */
    public function setTags(array $tags): void
    {
        $this->createIssueTags($tags);
        $this->removeIssueTags($tags);

        $this->entityManager->flush();

        $event = new SetIssueTagsEvent(
            issueId: $this->issue->getId()->integerId(),
            tags: $tags
        );

        $this->eventPersister->createIssueEvent(
            $event,
            $this->issue
        );
    }

    /**
     * @param string[] $tags
     * @return void
     */
    private function createIssueTags(array $tags): void
    {
        $currentTags = $this->issue->getTags()->map(fn(ProjectTag $tag) => $tag->getName())->toArray();

        $tagsToAdd = array_diff($tags, $currentTags);

        $tagsToCreate = $this->findProjectTags($tagsToAdd);

        foreach ($tagsToCreate as $tagToCreate) {
            $this->issue->addTag($tagToCreate);
        }
    }

    /**
     * @param string[] $tags
     * @return void
     */
    private function removeIssueTags(array $tags): void
    {
        $tagsToRemove = $this->issue->getTags()->filter(fn(ProjectTag $tag) => !in_array($tag->getName(), $tags));

        foreach ($tagsToRemove as $tagToRemove) {
            $this->issue->removeTag($tagToRemove);
        }
    }

    /**
     * @param string[] $tags
     * @return ProjectTag[]
     */
    private function findProjectTags(array $tags): array
    {
        return $this->projectTagRepository->findBy([
            'name' => $tags,
            'project' => $this->issue->getProject()
        ]);
    }
}
