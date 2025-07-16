<?php

namespace App\Controller\Admin;

use App\Action\User\CreateUser;
use App\Action\User\DeactivateUser;
use App\Action\User\ListUsers;
use App\Action\User\UpdateUser;
use App\Controller\Controller;
use App\Entity\User\User;
use App\Form\User\CommonUserForm;
use App\Form\User\CommonUserType;
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
        $form = $this->createForm(CommonUserType::class);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $createUser->execute($form->getData());

            $this->addFlash('success', 'User successfully created. Welcome email was sent to user.');
            return $this->redirectToRoute('app_admin_user_edit', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit')]
    public function edit(UpdateUser $updateUser, User $user, Request $request): Response
    {
        $form = $this->createForm(CommonUserType::class, new CommonUserForm(
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $updateUser->execute($form->getData(), $user);

            $this->addFlash('success', 'User successfully updated.');
            return $this->redirectToRoute('app_admin_user_edit', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('user/update.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/users/{id}/deactivate', name: 'app_admin_user_deactivate', methods: ['POST'])]
    public function deactivate(DeactivateUser $deactivateUser, User $user): Response
    {
        $deactivateUser->execute($user);

        $this->successFlash('User has been deactivated.');

        return $this->redirectToRoute('app_admin_user_edit', [
            'id' => $user->getId()
        ]);
    }

}
