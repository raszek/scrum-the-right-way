<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Formulate\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $response ??= new Response();

        if (200 === $response->getStatusCode()) {
            foreach ($parameters as $v) {
                if ($v instanceof Form && $v->hasErrors()) {
                    $response->setStatusCode(422);
                    break;
                }
            }
        }

        return parent::render($view, $parameters, $response);
    }
}
