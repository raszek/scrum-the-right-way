<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Formulate\CannotLoadFormException;
use App\Formulate\CannotValidateFormException;
use App\Formulate\Form;
use App\Helper\JsonHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

    public function validate(Form $form, Request $request): void
    {
        if (!$form->loadRequest($request)) {
            throw new CannotLoadFormException();
        }

        if ($form->validate()) {
            return;
        }

        throw new CannotValidateFormException($form);
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
