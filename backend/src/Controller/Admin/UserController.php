<?php

namespace App\Controller\Admin;

use App\Action\User\ListUsers;
use App\Controller\Controller;
use App\Form\Site\RegisterType;
use App\Table\QueryParams;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class UserController extends Controller
{

    #[Route('/users', name: 'app_admin_user_list')]
    public function index(ListUsers $listUsers, Request $request): Response
    {
        $queryParams = QueryParams::fromRequest($request);

        $table = $listUsers->execute($queryParams);

        return $this->render('user/index.html.twig', [
            'table' => $table
        ]);
    }

    public function create(Request $request)
    {
        $registerForm = $this->createForm(RegisterType::class);

        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            /**
             * @var RegisterForm $data
             */
            $data = $registerForm->getData();
            $this->siteService->register($data);
            $this->entityManager->flush();

            $this->addFlash('success', 'You successfully created account. Confirmation mail was sent to your email address.');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('site/register.html.twig', [
            'registerForm' => $registerForm
        ]);
    }

}
