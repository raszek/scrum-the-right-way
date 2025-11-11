<?php

namespace App\Controller\Issue;

use App\Controller\Controller;
use App\Helper\JsonHelper;
use App\Helper\StimulusHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IssueWidgetController extends Controller
{

    #[Route('/widgets/todo-list', name: 'widget_todo_list', methods: ['GET', 'POST'])]
    public function todoListWidget(Request $request): Response
    {
        $jsonState = $request->get('state');
        if (!$jsonState) {
            throw new BadRequestHttpException('No state provided');
        }

        $state = JsonHelper::decode($jsonState);

        return $this->render('issue/todo.html.twig', [
            'attributes' => StimulusHelper::object($state),
            'state' => $state,
        ]);
    }

}
