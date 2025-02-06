<?php

namespace App\Controller;



use App\Service\Issue\Session\IssueSessionSettings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user-settings')]
class UserSettingsController extends Controller
{

    public function __construct(
        readonly private IssueSessionSettings $issueSessionSettings,
    ) {
    }

    #[Route('/activities-visible')]
    #[IsGranted('ROLE_USER')]
    public function setIssueActivitiesVisible(Request $request): Response
    {
        $state = $request->get('state');

        if (!$state) {
            throw new UnprocessableEntityHttpException('State cannot be empty.');
        }

        $this->issueSessionSettings->setActivitiesVisible($state === 'true');

        return new Response(status: 204);
    }

}
