<?php

namespace App\Controller\User;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends Controller
{

    #[Route('/profile', 'app_user_profile')]
    public function profile(): Response
    {
    }
}
