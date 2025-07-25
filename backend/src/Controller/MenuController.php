<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Repository\User\UserNotificationRepository;
use App\Service\Menu\Provider\ProjectMenuDataProvider;
use App\Service\Menu\Provider\TopMenuDataProvider;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MenuController extends Controller
{

    public function __construct(
        private readonly ProjectMenuDataProvider $projectMenuDataProvider,
        private readonly TopMenuDataProvider $menuDataProvider,
        private readonly UserNotificationRepository $userNotificationRepository
    ) {
    }

    #[Route('/top-menu', name: 'app_menu')]
    public function topMenu(Request $request): Response
    {
        $currentPath = $request->get('currentPath');

        if (!$currentPath) {
            throw new Exception('Current path must be set');
        }

        $unreadNotificationCount = $this->userNotificationRepository->unreadNotificationCountForUser($this->getLoggedInUser());

        return $this->render('menu/top_menu.html.twig', [
            'links' => $this->menuDataProvider->getLinks($currentPath, $this->getLoggedInUser()),
            'unreadNotificationCount' => $unreadNotificationCount
        ]);
    }

    #[Route('/app/projects/{id}/sidebar', name: 'app_project_sidebar')]
    public function sidebar(Project $project, Request $request): Response
    {
        $currentPath = $request->get('currentPath');

        if (!$currentPath) {
            throw new Exception('Current path must be set');
        }

        return $this->render('menu/sidebar.html.twig', [
            'links' => $this->projectMenuDataProvider->getLinks($project, $currentPath)
        ]);
    }


}
