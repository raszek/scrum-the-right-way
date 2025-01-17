<?php

namespace App\Service\Menu;

use App\Entity\Project\Project;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class ProjectMenuDataProvider
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getLinks(Project $project, string $currentPath): array
    {
        $links = [];
        foreach ($this->getMenuLinks($project) as $menuLink) {
            $links[] = [
                'isActive' => str_contains($currentPath, $menuLink['url']),
                ...$menuLink
            ];
        }

        return $links;
    }

    private function getMenuLinks(Project $project): array
    {
        return [
            [
                'url' => $this->urlGenerator->generate('app_project_backlog', [
                    'id' => $project->getId()
                ]),
                'label' => 'Backlog',
                'icon' => 'bi-backpack',
            ],
            [
                'url' => $this->urlGenerator->generate('app_project_kanban', [
                    'id' => $project->getId()
                ]),
                'label' => 'Kanban',
                'icon' => 'bi-kanban',
            ],
            [
                'url' => $this->urlGenerator->generate('app_project_thread_list', [
                    'id' => $project->getId()
                ]),
                'label' => 'Threads',
                'icon' => 'bi-chat-left',
            ],
            [
                'url' => $this->urlGenerator->generate('app_project_activities', [
                    'id' => $project->getId()
                ]),
                'label' => 'Activities',
                'icon' => 'bi-activity',
            ],
            [
                'url' => $this->urlGenerator->generate('app_project_members', [
                    'id' => $project->getId()
                ]),
                'label' => 'Members',
                'icon' => 'bi-people',
            ],
            [
                'url' => $this->urlGenerator->generate('app_project_issue_list', [
                    'id' => $project->getId()
                ]),
                'label' => 'Issues',
                'icon' => 'bi-ui-checks',
            ],
        ];
    }

}
