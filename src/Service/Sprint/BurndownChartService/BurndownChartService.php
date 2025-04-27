<?php

namespace App\Service\Sprint\BurndownChartService;

use App\Entity\Sprint\Sprint;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonPeriod;
use DateTimeImmutable;

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
        $sprintEndDate = $this->getSprintEndDate($sprint);

        $period = CarbonPeriod::create($sprint->getStartedAt(), '1 day', $sprintEndDate);

        $now = $this->clock->now();

        $records = [];
        foreach ($period as $date) {
            $records[$date->format('Y-m-d')] = [
                'date' => $date->format('d.m'),
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
            $sprintStoryPoints -= $record['storyPoints'];
            $chartData[] = new BurndownChartRecord(
                date: $record['date'],
                storyPoints: isset($record['storyPoints'])
                    ? $sprintStoryPoints
                    : null,
            );
        }

        return $chartData;
    }

    private function getSprintEndDate(Sprint $sprint): DateTimeImmutable
    {
        if ($sprint->isFinished()) {
            return $sprint->getEndedAt();
        }

        $now = $this->clock->now();

        return $now->greaterThan($sprint->getEstimatedEndDate())
            ? $now
            : $sprint->getEstimatedEndDate();
    }
}
