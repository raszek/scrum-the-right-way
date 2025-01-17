<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Form\Project\ProjectFormType;
use App\Repository\Project\ProjectRepository;
use App\Service\Project\ProjectEditorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/projects')]
#[IsGranted('ROLE_USER')]
class ProjectController extends Controller
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectRepository $projectRepository,
        private readonly ProjectEditorFactory $projectEditorFactory
    ) {
    }

    #[Route('', name: 'app_project_list')]
    public function index(): Response
    {
        $projects = $this->projectRepository->projectList($this->getLoggedInUser());

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/create', name: 'app_project_create')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(ProjectFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var Project $createdProject
             */
            $createdProject = $form->getData();
            $this->entityManager->persist($createdProject);
            $projectEditor = $this->projectEditorFactory->create($createdProject, $this->getLoggedInUser());
            $projectEditor->addMember($this->getLoggedInUser());

            $this->addFlash('success', sprintf('Project "%s" successfully created.', $createdProject->getName()));
            return $this->redirectToRoute('app_project_list');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form
        ]);
    }
}
