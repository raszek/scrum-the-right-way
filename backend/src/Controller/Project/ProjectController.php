<?php

namespace App\Controller\Project;

use App\Action\Project\CreateProject;
use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Form\Project\ProjectForm;
use App\Repository\Project\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/projects')]
#[IsGranted('ROLE_USER')]
class ProjectController extends Controller
{

    public function __construct(
        private readonly ProjectRepository $projectRepository,
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
    public function create(
        Request $request,
        ProjectForm $projectForm,
        CreateProject $createProject
    ): Response {
        $form = $projectForm->create();

        if ($form->loadRequest($request) && $form->validate()) {

            $createdProject = $createProject->execute($form->getData(), $this->getLoggedInUser());

            $this->addFlash('success', sprintf('Project "%s" successfully created.', $createdProject->getName()));
            return $this->redirectToRoute('app_project_list');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form,

        ]);
    }

    #[Route('/projects/{id}/home', name: 'app_project_home')]
    public function home(Project $project): Response
    {
        if ($project->isScrum()) {
            return $this->redirectToRoute('app_project_scrum_home', [
                'id' => $project->getId()
            ]);
        }

        throw new NotFoundHttpException('Not implemented project type home');
    }
}
