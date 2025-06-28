<?php

namespace App\Service\Room;

use App\Entity\Issue\Issue;
use App\Enum\Issue\IssueTypeEnum;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\Room\RoomIssueRepository;

readonly class RoomService
{

    public function __construct(
        private IssueRepository $issueRepository,
        private RoomIssueRepository $roomIssueRepository,
    ) {
    }

    /**
     * @param string $search
     * @param string $roomId
     * @return array
     */
    public function searchIssues(string $search, string $roomId): array
    {
        $roomIds = $this->roomIssueRepository->findRoomIssueIds($roomId);

        $query = $this->issueRepository->createQueryBuilder('issue');

        $query
            ->notIn('issue.id', $roomIds)
            ->fuzzyLike("CONCAT(issue.title, ' #', issue.number)", $search)
            ->andWhere('issue.type <> :type')
            ->setParameter('type', IssueTypeEnum::Feature)
            ->setMaxResults(10);

        $issues = $query->getQuery()->getResult();

        return ArrayHelper::map($issues, fn(Issue $issue) => [
            'id' => $issue->getId()->get(),
            'title' => $issue->getTitleWithCode(),
            'storyPoints' => $issue->getStoryPoints(),
        ]);
    }

}
