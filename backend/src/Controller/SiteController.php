<?php

namespace App\Controller;

use App\Exception\Site\UserNotFoundException;
use App\Form\Site\ForgotPasswordForm;
use App\Form\Site\ForgotPasswordType;
use App\Form\Site\ResetPasswordForm;
use App\Form\Site\ResetPasswordType;
use App\Repository\User\UserRepository;
use App\Service\Site\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SiteController extends Controller
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly SiteService $siteService
    ) {
    }

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
    public function activateAccount(string $email, string $activationCode): Response
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'activationCode' => $activationCode
        ]);

        if (!$user) {
            throw new NotFoundHttpException('Page not found');
        }

        $this->siteService->activateUser($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Account successfully activated');

        return $this->redirect('/login');
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->setResetPasswordCode($form->getData());

            $this->addFlash('warning', 'Email was sent to your inbox if your account exist');
        }

        return $this->render('site/forgot_password.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/reset-password/{email}/{resetPasswordCode}', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        string $resetPasswordCode,
        string $email
    ): Response {
        $resetPasswordData = new ResetPasswordForm(
            resetPasswordCode: $resetPasswordCode,
            email: $email
        );
        $resetPasswordForm = $this->createForm(ResetPasswordType::class, $resetPasswordData);

        $resetPasswordForm->handleRequest($request);
        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {

            try {
                $this->siteService->resetPassword($resetPasswordForm->getData());
            } catch (UserNotFoundException) {
                throw new BadRequestException('Could not reset password');
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Successfully reset password!');
            return $this->redirect('/login');
        }

        return $this->render('site/reset_password.html.twig', [
            'form' => $resetPasswordForm
        ]);
    }

    private function setResetPasswordCode(ForgotPasswordForm $forgotPasswordForm): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $forgotPasswordForm->email
        ]);

        if (!$user) {
            return;
        }

        $this->siteService->setResetPasswordCode($user);
        $this->entityManager->flush();
    }
}
