<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Form\Event\SearchEventForm;
use App\Form\Event\SearchEventType;
use App\Repository\Event\EventRepository;
use App\Security\Voter\EventVoter;
use App\Service\Event\EventService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends Controller
{

    public function __construct(
        private readonly EventService $eventService,
        private readonly PaginatorInterface $paginator,
        private readonly EventRepository $eventRepository
    ) {
    }

    #[Route('/projects/{id}/activities', name: 'app_project_activities')]
    public function index(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_LIST, $project);

        $searchFormData  = new SearchEventForm($project);
        $searchForm = $this->createForm(SearchEventType::class, $searchFormData);

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
        }

        $pagination = $this->paginator->paginate(
            target: $this->eventRepository->listQuery($project, $searchFormData),
            page: $request->get('page') ?? 1,
            limit: 20
        );

        $events = $this->eventService->getEventRecords($pagination->getItems());

        return $this->render('event/index.html.twig', [
            'pagination' => $pagination,
            'events' => $events,
            'project' => $project,
            'searchForm' => $searchForm
        ]);
    }

}
