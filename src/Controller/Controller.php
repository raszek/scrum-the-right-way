<?php

namespace App\Controller;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    public function getLoggedInUser(): User
    {
        return $this->getUser();
    }

    public function errorFlash(string $message): void
    {
        $this->addFlash('danger', $message);
    }

    public function successFlash(string $message): void
    {
        $this->addFlash('success', $message);
    }
}
