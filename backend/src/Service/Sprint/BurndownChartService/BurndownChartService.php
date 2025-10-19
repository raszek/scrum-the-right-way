<?php

namespace App\Service\Sprint\BurndownChartService;

use App\Entity\Sprint\Sprint;
use App\Repository\Sprint\SprintGoalIssueRepository;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use DateTimeImmutable;
use RuntimeException;

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
    public function getCurrentSprintChartData(Sprint $sprint): array
    {
        $sprintEndDate = $this->clock->now()->greaterThan($sprint->getEstimatedEndDate())
            ? $this->clock->now()
            : CarbonImmutable::instance($sprint->getEstimatedEndDate());

        $databaseRecords = $this->sprintGoalIssueRepository->getCurrentSprintGroupedStoryPoints(
            $sprint,
            $sprintEndDate
        );

        $records = $this->getSprintDates($sprint->getStartedAt(), $sprintEndDate);

        foreach ($databaseRecords as $databaseRecord) {
            if ($databaseRecord['finishedday'] === null) {
                continue;
            }

            $records[$databaseRecord['finishedday']]['storyPoints'] = $databaseRecord['storypoints'];
        }

        $sprintStoryPoints = $this->sprintGoalIssueRepository->getCurrentSprintStoryPoints($sprint);

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

    public function getHistorySprintChartData(Sprint $sprint): array
    {
        $sprintEndDate = $sprint->getEndedAt();
        if (!$sprintEndDate) {
            throw new RuntimeException('Sprint has not ended yet');
        }

        $databaseRecords = $this->sprintGoalIssueRepository->getHistorySprintGroupedStoryPoints($sprint);

        $records = $this->getSprintDates($sprint->getStartedAt(), $sprintEndDate);

        foreach ($databaseRecords as $databaseRecord) {
            if ($databaseRecord['finishedday'] === null) {
                continue;
            }

            $records[$databaseRecord['finishedday']]['storyPoints'] = $databaseRecord['storypoints'];
        }

        $sprintStoryPoints = $this->sprintGoalIssueRepository->getHistorySprintStoryPoints($sprint);

        $chartData = [
            new BurndownChartRecord(
                date: $sprint->getStartedAt()->format('m.d'),
                storyPoints: $sprintStoryPoints
            ),
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

    private function getSprintDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        $now = $this->clock->now();

        $records = [];
        foreach ($period as $date) {
            $records[$date->format('Y-m-d')] = [
                'date' => $date->format('d.m'),
                'storyPoints' => $date->greaterThan($now) ? null : 0,
            ];
        }

        return $records;
    }
}
