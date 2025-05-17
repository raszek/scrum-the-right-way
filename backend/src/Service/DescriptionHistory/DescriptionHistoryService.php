<?php

namespace App\Service\DescriptionHistory;

use App\Entity\Issue\DescriptionHistory;
use App\Entity\Issue\Issue;
use App\Repository\Issue\DescriptionHistoryRepository;
use Jfcherng\Diff\Factory\RendererFactory;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

readonly class DescriptionHistoryService
{

    public function __construct(
        private DescriptionHistoryRepository $descriptionHistoryRepository,
        private PaginatorInterface $paginator
    ) {
    }

    public function getItems(Issue $issue, int $page = 1): PaginationInterface
    {
        return $this->paginator->paginate(
            target: $this->descriptionHistoryRepository->itemsQuery($issue),
            page: $page,
            limit: 10
        );
    }

    public function getChanges(DescriptionHistory $descriptionHistory): string
    {
        if (!$descriptionHistory->getChanges()) {
            return '';
        }

        $htmlRenderer = RendererFactory::make('Inline');
        return $htmlRenderer->renderArray($descriptionHistory->getChanges());
    }
}
