<?php

namespace App\Service\Issue\Session;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class IssueSessionSettings
{

    CONST ACTIVITIES_VISIBLE_KEY = 'activities-visible';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }


    public function isActivitiesVisible(): bool
    {
        $session = $this->requestStack->getSession();

        return $session->get(self::ACTIVITIES_VISIBLE_KEY) === 'true';
    }

    public function setActivitiesVisible(bool $isVisible): void
    {
        $session = $this->requestStack->getSession();

        $session->set(self::ACTIVITIES_VISIBLE_KEY, $isVisible ? 'true' : 'false');
    }
}
