<?php

namespace App\Controller;

use App\Entity\Project\Project;
use App\Entity\Thread\Thread;
use App\Exception\Thread\ThreadAlreadyClosedException;
use App\Exception\Thread\ThreadAlreadyOpenedException;
use App\Form\Thread\MessageType;
use App\Form\Thread\SearchThreadForm;
use App\Form\Thread\SearchThreadType;
use App\Form\Thread\ThreadForm;
use App\Form\Thread\ThreadType;
use App\Repository\Thread\ThreadMessageRepository;
use App\Repository\Thread\ThreadRepository;
use App\Security\Voter\ThreadVoter;
use App\Service\Thread\ThreadEditorFactory;
use App\Service\Thread\ThreadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects/{id}/threads')]
class ThreadController extends Controller
{

    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly ThreadMessageRepository $threadMessageRepository,
        private readonly ThreadService $threadService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaginatorInterface $paginator,
        private readonly ThreadEditorFactory $threadEditorFactory,
    ) {
    }

    #[Route('', name: 'app_project_thread_list')]
    public function index(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ThreadVoter::THREAD_LIST, $project);

        $searchFormData = new SearchThreadForm();
        $searchForm = $this->createForm(SearchThreadType::class, $searchFormData);

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchFormData = $searchForm->getData();
        }

        $query = $this->threadRepository->threadsQuery($project, $searchFormData);

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $threads = $this->threadService->getThreadRecords($pagination->getItems());

        return $this->render('thread/index.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'threads' => $threads,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/create', name: 'app_project_thread_create')]
    public function create(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ThreadVoter::THREAD_CREATE, $project);

        $formData = new ThreadForm(
            project: $project,
            createdBy: $this->getLoggedInUser()
        );

        $form = $this->createForm(ThreadType::class, $formData);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->threadService->createThread($form->getData());

            return $this->redirectToRoute('app_project_thread_list', [
                'id' => $project->getId(),
            ]);
        }

        return $this->render('thread/create.html.twig', [
            'project' => $project,
            'form' => $form
        ]);
    }

    #[Route('/{threadId}/{slug}/messages', name: 'app_project_thread_messages')]
    public function messages(Project $project, string $threadId, string $slug, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ThreadVoter::THREAD_MESSAGES, $project);

        $thread = $this->threadRepository->findOneBy([
            'id' => $threadId,
            'slug' => $slug
        ]);

        if (!$thread) {
            throw new NotFoundHttpException('Thread not found');
        }

        $form = $this->createForm(MessageType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $threadEditor = $this->threadEditorFactory->create($thread, $this->getLoggedInUser());
            $threadEditor->addMessage($form->getData());
            $this->entityManager->flush();

            return $this->redirectToRoute('app_project_thread_messages', [
                'id' => $project->getId(),
                'threadId' => $thread->getId(),
                'slug' => $thread->getSlug()
            ]);
        }

        $messages = $this->threadMessageRepository->getThreadMessages($thread);

        return $this->render('thread/messages.html.twig', [
            'project' => $project,
            'thread' => $thread,
            'messages' => $messages,
            'form' => $form
        ]);
    }

    #[Route('/{threadId}/close', name: 'app_project_thread_close')]
    public function close(Project $project, string $threadId): Response
    {
        $this->denyAccessUnlessGranted(ThreadVoter::THREAD_CLOSE, $project);

        $thread = $this->findThread($threadId);

        try {
            $threadEditor = $this->threadEditorFactory->create($thread, $this->getLoggedInUser());
            $threadEditor->close();
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Closed thread "%s". You can open it again if needed.', $thread->getTitle()));
        } catch (ThreadAlreadyClosedException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_project_thread_list', [
            'id' => $project->getId()
        ]);
    }

    #[Route('/{threadId}/open', name: 'app_project_thread_open')]
    public function open(Project $project, string $threadId): Response
    {
        $this->denyAccessUnlessGranted(ThreadVoter::THREAD_REOPEN, $project);

        $thread = $this->findThread($threadId);

        try {
            $threadEditor = $this->threadEditorFactory->create($thread, $this->getLoggedInUser());
            $threadEditor->open();
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Reopened thread "%s".', $thread->getTitle()));
        } catch (ThreadAlreadyOpenedException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_project_thread_list', [
            'id' => $project->getId()
        ]);
    }

    private function findThread(string $threadId): Thread
    {
        $thread = $this->threadRepository->find($threadId);

        if (!$thread) {
            throw new NotFoundHttpException('Thread not found');
        }

        return $thread;
    }

}
