<?php

namespace App\Controller\User;

use App\Action\Profile\UpdateProfile;
use App\Controller\Controller;
use App\Form\Profile\ProfileForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends Controller
{

    #[Route('/profile', 'app_user_profile')]
    public function profile(ProfileForm $form, Request $request, UpdateProfile $updateProfile): Response
    {
        $profileForm = $form->create($this->getLoggedInUser());

        if ($profileForm->loadRequest($request) && $profileForm->validate()) {

            $updateProfile->execute($profileForm->getData(), $this->getLoggedInUser());

            $this->successFlash('Profile successfully updated.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $profileForm
        ]);
    }
}
