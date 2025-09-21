<?php

namespace App\Action\Sprint;

use App\Entity\Sprint\Sprint;
use App\Exception\Sprint\CannotUpdateEstimatedEndDateException;
use App\Service\Common\ClockInterface;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEstimatedEndDate
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock
    ) {
    }

    public function execute(Sprint $sprint, CarbonImmutable $estimatedEndDate): void
    {
        if (!$sprint->isCurrent()) {
            throw new CannotUpdateEstimatedEndDateException('Cannot update estimated end date for not current sprint.');
        }

        $now = $this->clock->now()->startOfDay();

        if ($estimatedEndDate->lessThan($now)) {
            throw new CannotUpdateEstimatedEndDateException('Cannot update estimated end date to a date in the past.');
        }

        $sprint->setEstimatedEndDate($estimatedEndDate);
        $this->entityManager->flush();
    }
}
