<?php

namespace App\Controller\User;

use App\Action\Profile\ChangeAvatar;
use App\Action\Profile\ChangeEmail;
use App\Action\Profile\ChangePassword;
use App\Action\Profile\ConfirmChangeEmail;
use App\Action\Profile\UpdateProfile;
use App\Controller\Controller;
use App\Entity\User\User;
use App\Exception\Profile\CannotChangeEmailException;
use App\Form\Profile\AvatarForm;
use App\Form\Profile\ChangeEmailForm;
use App\Form\Profile\ChangePasswordForm;
use App\Form\Profile\ProfileForm;
use App\Formulate\CannotValidateFormException;
use App\Service\File\FileService;
use App\Service\Menu\Profile\ProfileMenu;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends Controller
{

    #[Route('/profile', 'app_user_profile')]
    public function profile(ProfileForm $form, Request $request, UpdateProfile $updateProfile): Response
    {
        $profileForm = $form->create($this->getLoggedInUser());

        $profile = $this->getLoggedInUser()->getProfile();

        if ($profileForm->loadRequest($request) && $profileForm->validate()) {

            $updateProfile->execute($profileForm->getData(), $this->getLoggedInUser());

            $this->successFlash('Profile successfully updated.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $profileForm,
            'profile' => $profile,
        ]);
    }


    #[Route('/profile/change-password', 'app_user_profile_change_password')]
    public function changePassword(
        ChangePasswordForm $changePasswordForm,
        Request $request,
        ChangePassword $changePassword
    ): Response {
        $form = $changePasswordForm->create($this->getLoggedInUser());

        if ($form->loadRequest($request) && $form->validate()) {
            $changePassword->execute($form->getData(), $this->getLoggedInUser());

            $this->successFlash('Password successfully changed.');
            return $this->redirectToRoute('app_user_profile_change_password');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/change-email', 'app_user_profile_change_email')]
    public function changeEmail(
        ChangeEmailForm $changeEmailForm,
        Request $request,
        ChangeEmail $changeEmail,
    ): Response {
        $form = $changeEmailForm->create($this->getLoggedInUser());

        if ($form->loadRequest($request) && $form->validate()) {
            $changeEmail->execute($form->getData(), $this->getLoggedInUser());

            $this->successFlash('Email sent was to your inbox. Confirm changing your email address.');
            return $this->redirectToRoute('app_user_profile_change_email');
        }

        return $this->render('profile/change_email.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/confirm-change-email/{activationCode}', 'app_user_profile_confirm_change_email', methods: ['GET'])]
    public function confirmChangeEmail(string $activationCode, ConfirmChangeEmail $confirmChangeEmail): Response
    {
        try {
            $confirmChangeEmail->execute($activationCode, $this->getLoggedInUser());
        } catch (CannotChangeEmailException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $this->successFlash('Email successfully changed.');
        return $this->redirectToRoute('app_user_profile_change_email');
    }

    #[Route('/profile/avatar', 'app_user_profile_change_avatar', methods: ['POST'])]
    public function changeAvatar(
        AvatarForm $avatarForm,
        Request $request,
        ChangeAvatar $changeAvatar,
    ): Response {
        $form = $avatarForm->create();

        if (!$form->loadRequest($request)) {
            throw new BadRequestException('Form cannot be loaded');
        }

        if (!$form->validate()) {
            throw new CannotValidateFormException($form);
        }

        $profile = $changeAvatar->execute($form->getData(), $this->getLoggedInUser());

        return $this->render('profile/avatar.html.twig', [
            'profile' => $profile,
        ]);
    }

    #[Route('/profile/avatar', 'app_user_profile_show_avatar', methods: ['GET'])]
    public function showAvatar(FileService $fileService): BinaryFileResponse
    {
        $avatar = $this->getLoggedInUser()->getProfile()->getAvatar();

        if (!$avatar) {
            throw new NotFoundHttpException('Avatar not found');
        }

        $avatar = $fileService->getFilePath($avatar);

        return new BinaryFileResponse($avatar);
    }

    #[Route('/profile/menu', 'app_user_profile_menu')]
    public function profileMenu(ProfileMenu $profileMenu, Request $request): Response
    {
        $currentPath = $request->get('currentPath');

        if (!$currentPath) {
            throw new Exception('Current path must be set');
        }

        return $this->render('profile/_menu.html.twig', [
            'menu' => $profileMenu->create($currentPath)
        ]);
    }
}
