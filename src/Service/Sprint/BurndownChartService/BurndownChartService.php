<?php

namespace App\Service\Sprint\BurndownChartService;

use App\Entity\Sprint\Sprint;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use function PHPUnit\Framework\lessThan;

readonly class BurndownChartService
{

    public function __construct(
        private SprintGoalIssueRepository $sprintGoalIssueRepository,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param Sprint $sprint
     * @return BurndownChartRecord[]
     */
    public function getChartData(Sprint $sprint): array
    {
        $now = $this->clock->now();

        $sprintEndDate = $now->greaterThan($sprint->getEstimatedEndDate())
            ? $now
            : $sprint->getEstimatedEndDate();

        $period = CarbonPeriod::create($sprint->getStartedAt(), '1 day', $sprintEndDate);

        $records = [];
        foreach ($period as $date) {
            $records[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'storyPoints' => $date->greaterThan($now) ? null : 0,
            ];
        }

        $databaseRecords = $this->sprintGoalIssueRepository->getGroupedStoryPoints(
            $sprint,
            $sprintEndDate
        );

        foreach ($databaseRecords as $databaseRecord) {
            $records[$databaseRecord['finishedday']]['storyPoints'] = $databaseRecord['storypoints'];
        }

        $sprintStoryPoints = $this->sprintGoalIssueRepository->getSprintStoryPoints($sprint);

        $chartData = [
            new BurndownChartRecord(date: 'Start', storyPoints: $sprintStoryPoints),
        ];

        foreach ($records as $record) {
            $chartData[] = new BurndownChartRecord(
                date: $record['date'],
                storyPoints: isset($record['storyPoints'])
                    ? $sprintStoryPoints - $record['storyPoints']
                    : null,
            );
        }

        return $chartData;
    }

}
