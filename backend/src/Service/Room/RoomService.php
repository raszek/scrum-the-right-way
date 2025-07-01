<?php

namespace App\Service\Room;

use App\Entity\Issue\Issue;
use App\Enum\Issue\IssueTypeEnum;
use App\Helper\ArrayHelper;
use App\Repository\Issue\IssueRepository;
use App\Repository\Room\RoomIssueRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class RoomService
{

    public function __construct(
        private IssueRepository $issueRepository,
        private RoomIssueRepository $roomIssueRepository,
        private UrlGeneratorInterface $urlGenerator
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
            ->fuzzyLike("CONCAT('[#', issue.number, '] ', issue.title)", $search)
            ->andWhere('issue.type <> :type')
            ->setParameter('type', IssueTypeEnum::Feature)
            ->setMaxResults(10);

        $issues = $query->getQuery()->getResult();

        return ArrayHelper::map($issues, fn(Issue $issue) => [
            'value' => $issue->getId()->get(),
            'text' => $issue->prefixCodeTitle(),
            'storyPoints' => $issue->getStoryPoints(),
            'url' => $this->urlGenerator->generate('app_project_room_issue_view', [
                'id' => $issue->getProject()->getId()->get(),
                'roomId' => $roomId,
                'issueId' => $issue->getId()->get(),
            ]),
        ]);
    }

}
