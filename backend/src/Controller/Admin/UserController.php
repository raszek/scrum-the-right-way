<?php

namespace App\Controller\Admin;

use App\Action\User\CreateUser;
use App\Action\User\ListUsers;
use App\Controller\Controller;
use App\Form\User\CreateUserForm;
use App\Form\User\CreateUserType;
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

    #[Route('/users/create', name: 'app_admin_user_create')]
    public function create(CreateUser $createUser, Request $request): Response
    {
        $form = $this->createForm(CreateUserType::class);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $createUser->execute($form->getData());

            $this->addFlash('success', 'User successfully created. Welcome email was sent to user.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form
        ]);
    }

}
