<?php

namespace App\Controller\Project;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Form\Tag\NewTagForm;
use App\Security\Voter\ProjectTagVoter;
use App\Service\Common\FormValidator;
use App\Service\Tag\ProjectTagEditorFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class ProjectTagController extends Controller
{

    public function __construct(
        private readonly FormValidator $formValidator,
        private readonly ProjectTagEditorFactory $projectTagEditorFactory,
    ) {
    }

    #[Route('/tags', name: 'app_project_create_tag')]
    public function create(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProjectTagVoter::CREATE_PROJECT_TAG, $project);

        $form = NewTagForm::fromRequest($request, $project);

        $this->formValidator->validate($form);

        $projectTagEditor = $this->projectTagEditorFactory->create($project);

        $projectTagEditor->addTag($form);

        return new Response(status: 204);
    }

}
