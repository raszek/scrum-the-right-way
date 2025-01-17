<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectMember;
use App\Entity\User\User;
use App\Enum\Project\ProjectRoleEnum;
use App\Exception\Project\CannotAddProjectMemberException;
use App\Exception\Project\CannotRemoveProjectMemberException;
use App\Exception\Project\ProjectMemberCannotAddRoleException;
use App\Exception\Project\ProjectMemberCannotRemoveRoleException;
use App\Form\Project\AddProjectMemberForm;
use App\Form\Project\AddProjectMemberType;
use App\Form\Project\ProjectMemberSearchType;
use App\Helper\ArrayHelper;
use App\Repository\Project\ProjectMemberRepository;
use App\Repository\User\UserRepository;
use App\Security\Voter\ProjectMemberVoter;
use App\Service\Project\ProjectEditorFactory;
use App\Service\Project\ProjectMemberEditorFactory;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}')]
class ProjectMemberController extends Controller
{

    public function __construct(
        private readonly ProjectMemberRepository $projectMemberRepository,
        private readonly PaginatorInterface $paginator,
        private readonly ProjectMemberEditorFactory $projectMemberEditorFactory,
        private readonly ProjectEditorFactory $projectEditorFactory,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/members', name: 'app_project_members')]
    public function index(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::MEMBER_LIST, $project);

        $data = new AddProjectMemberForm($project);
        $addProjectMemberForm = $this->createForm(AddProjectMemberType::class, $data);

        $addProjectMemberForm->handleRequest($request);
        if ($addProjectMemberForm->isSubmitted() && $addProjectMemberForm->isValid()) {
            return $this->handleAddMember($project, $addProjectMemberForm);
        }

        $searchForm = $this->createForm(ProjectMemberSearchType::class);
        $searchForm->handleRequest($request);
        $filters = null;
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();
        }

        $pagination = $this->paginator->paginate(
            $this->projectMemberRepository->projectMembersQuery($project, $filters),
            $request->query->get('page', 1),
            10,
        );

        return $this->render('project_member/index.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'loggedInMember' => $project->member($this->getLoggedInUser()),
            'addProjectMemberForm' => $addProjectMemberForm,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/non-members', name: 'app_project_non_members')]
    public function nonMembers(Project $project, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::PROJECT_SEARCH_NON_MEMBER, $project);

        $searchedName = $request->get('query') ?? throw new BadRequestHttpException('Required parameter "query" missing');

        $users = $this->userRepository->searchNonProjectUsers($project, $searchedName);

        $result = ArrayHelper::map($users, fn(User $user) => [
            'text' => $user->getDomainName(),
            'value' => $user->getEmail(),
        ]);

        return new JsonResponse([
            'results' => $result
        ]);
    }

    #[Route('/members/{projectMemberId}/remove', name: 'app_project_member_remove', methods: ['POST'])]
    public function removeMember(Project $project, string $projectMemberId): Response
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::PROJECT_REMOVE_MEMBER, $project);

        $projectMember = $this->findProjectMember($projectMemberId, $project);

        $removedMemberEmail = $projectMember->getEmail();

        $projectEditor = $this->projectEditorFactory->create($project, $this->getLoggedInUser());

        try {
            $projectEditor->removeMember($projectMember);
        } catch (CannotRemoveProjectMemberException $e) {
            $this->errorFlash($e->getMessage());

            return $this->redirectToRoute('app_project_members', [
                'id' => $project->getId(),
            ]);
        }

        $this->successFlash(sprintf('Successfully remove member %s from project.', $removedMemberEmail));

        return $this->redirectToRoute('app_project_members', [
            'id' => $project->getId(),
        ]);
    }

    #[Route('/members/{projectMemberId}/roles/{role}/add', name: 'app_project_members_role_add', methods: ['POST'])]
    public function addRole(Project $project, string $projectMemberId, string $role): Response
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::PROJECT_MEMBER_ADD_ROLE, $project);

        $projectMember = $this->findProjectMember($projectMemberId, $project);

        $projectMemberEditor = $this->projectMemberEditorFactory->create($projectMember, $this->getLoggedInUser());

        $projectRole = $this->getProjectRole($role);
        try {
            $projectMemberEditor->addRole($projectRole);
        } catch (ProjectMemberCannotAddRoleException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }

    #[Route('/members/{projectMemberId}/roles/{role}/remove', name: 'app_project_members_role_remove', methods: ['POST'])]
    public function removeRole(Project $project, string $projectMemberId, string $role): Response
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::PROJECT_MEMBER_REMOVE_ROLE, $project);

        $projectMember = $this->findProjectMember($projectMemberId, $project);

        $projectMemberEditor = $this->projectMemberEditorFactory->create($projectMember, $this->getLoggedInUser());

        $projectRole = $this->getProjectRole($role);
        try {
            $projectMemberEditor->removeRole($projectRole);
        } catch (ProjectMemberCannotRemoveRoleException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(status: 204);
    }

    private function getProjectRole(string $roleName): ProjectRoleEnum
    {
        return ProjectRoleEnum::tryFrom($roleName)
            ?? throw new BadRequestHttpException('Invalid role name '.$roleName);
    }

    private function findProjectMember(string $id, Project $project): ProjectMember
    {
        $projectMember =  $this->projectMemberRepository->findOneBy([
            'id' => $id,
            'project' => $project,
        ]);

        if (!$projectMember) {
            throw new NotFoundHttpException('Project member not found');
        }

        return $projectMember;
    }

    /**
     * @param Project $project
     * @param FormInterface $addProjectMemberForm
     * @return RedirectResponse
     */
    private function handleAddMember(Project $project, FormInterface $addProjectMemberForm): RedirectResponse
    {
        $this->denyAccessUnlessGranted(ProjectMemberVoter::PROJECT_ADD_MEMBER, $project);

        $projectEditor = $this->projectEditorFactory->create($project, $this->getLoggedInUser());
        $email = $addProjectMemberForm->getData()->email;

        $addedUser = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (!$addedUser) {
            throw new NotFoundHttpException('Added user not found');
        }

        try {
            $projectEditor->addMember($addedUser);
        } catch (CannotAddProjectMemberException $e) {
            $this->errorFlash($e->getMessage());

            return $this->redirectToRoute('app_project_members', [
                'id' => $project->getId(),
            ]);
        }

        $this->successFlash('Successfully added project member ' . $addedUser->getEmail());

        return $this->redirectToRoute('app_project_members', ['id' => $project->getId()]);
    }

}
