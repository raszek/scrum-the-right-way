<?php

namespace App\Service\Kanban;

use App\Enum\Kanban\KanbanFilterEnum;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class KanbanSession
{

    const KANBAN_FILTER_KEY = 'KANBAN_FILTER_KEY';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }


    public function getFilter(): KanbanFilterEnum
    {
        $session = $this->requestStack->getSession();

        return KanbanFilterEnum::tryFrom($session->get(self::KANBAN_FILTER_KEY)) ?? KanbanFilterEnum::Big;
    }

    public function setFilter(KanbanFilterEnum $enum): void
    {
        $session = $this->requestStack->getSession();

        $session->set(self::KANBAN_FILTER_KEY, $enum->value);
    }

}
