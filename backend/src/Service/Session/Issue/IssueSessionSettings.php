<?php

namespace App\Service\Session\Issue;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class IssueSessionSettings
{

    CONST string ACTIVITIES_VISIBLE_KEY = 'ACTIVITIES_VISIBLE_KEY';

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
