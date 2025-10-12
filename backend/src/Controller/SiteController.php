<?php

namespace App\Controller;

use App\Action\Site\ActivateUser;
use App\Action\Site\ConfirmResetPassword;
use App\Action\Site\ResetPassword;
use App\Exception\Site\CannotActivateUserException;
use App\Exception\Site\CannotResetPasswordException;
use App\Exception\Site\UserNotFoundException;
use App\Form\Site\ForgotPasswordForm;
use App\Form\Site\ResetPasswordFormData;
use App\Form\Site\ResetPasswordForm;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SiteController extends Controller
{

    #[Route(['', '/login'], name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_project_list');
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('site/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/activate-account/{email}/{activationCode}', name: 'app_activate_account')]
    public function activateAccount(
        ActivateUser $activateUser,
        ResetPasswordForm $resetPasswordForm,
        Request $request
    ): Response
    {
        if ($this->getUser()) {
            throw new BadRequestException('User is already logged in. Log out to activate account.');
        }

        $formData = new ResetPasswordFormData(
            resetPasswordCode: $request->get('activationCode'),
            email: $request->get('email')
        );

        $form = $resetPasswordForm->create($formData);
        if ($form->loadRequest($request) && $form->validate()) {
            try {
                $activateUser->execute($form->getData());
            } catch (CannotActivateUserException $e) {
                throw new BadRequestException($e->getMessage());
            }

            $this->addFlash('success', 'Account successfully activated. You can now log in.');
            return $this->redirect('/login');
        }

        return $this->render('site/reset_password.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        ResetPassword $resetPassword,
        ForgotPasswordForm $forgotPasswordForm,
    ): Response
    {
        $form = $forgotPasswordForm->create();

        if ($form->loadRequest($request) && $form->validate()) {
            try {
                $resetPassword->execute($form->getData());
            } catch (UserNotFoundException) {}

            $this->addFlash('warning', 'Email was sent to your inbox if your account exist.');
        }

        return $this->render('site/forgot_password.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/reset-password/{email}/{resetPasswordCode}', name: 'app_reset_password')]
    public function resetPassword(
        ConfirmResetPassword $resetPassword,
        ResetPasswordForm $resetPasswordForm,
        Request $request,
        string $resetPasswordCode,
        string $email
    ): Response {
        $resetPasswordData = new ResetPasswordFormData(
            resetPasswordCode: $resetPasswordCode,
            email: $email
        );

        $form = $resetPasswordForm->create($resetPasswordData);

        if ($form->loadRequest($request) && $form->validate()) {
            try {
                $resetPassword->execute($form->getData());
            } catch (CannotResetPasswordException $e) {
                throw new BadRequestException($e->getMessage());
            }

            $this->addFlash('success', 'Successfully reset password!');
            return $this->redirect('/login');
        }

        return $this->render('site/reset_password.html.twig', [
            'form' => $form
        ]);
    }
}
