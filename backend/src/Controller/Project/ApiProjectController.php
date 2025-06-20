<?php

namespace App\Controller\Project;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Repository\User\UserRepository;
use App\Service\Jwt\Websocket\WebsocketJwtServiceFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use UnexpectedValueException;

#[Route('/projects/{id}')]
class ApiProjectController extends Controller
{

    public function __construct(
        private readonly WebsocketJwtServiceFactory $websocketJwtServiceFactory,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/access', name: 'app_project_access')]
    public function access(Project $project, Request $request): Response
    {
        $jwtToken = $this->readJwtToken($request);

        $jwtService = $this->websocketJwtServiceFactory->create();

        try {
            $decoded = $jwtService->decode($jwtToken);
        } catch (UnexpectedValueException) {
            throw new UnauthorizedHttpException('JWT token is invalid');
        }

        $user = $this->userRepository->findOneBy(['id' => $decoded['id']]);
        if (!$user) {
            throw new UnauthorizedHttpException('User not found');
        }

        $foundMember = $project->findMember($user);
        if (!$foundMember) {
            throw new AccessDeniedHttpException('User is not a member of this project');
        }

        return new Response(status: 204);
    }

    private function readJwtToken(Request $request): string
    {
        $jwtToken = $request->headers->get('Authorization');

        if (!$jwtToken) {
            throw new UnauthorizedHttpException('JWT token is missing');
        }

        [$bearer, $token] = explode(' ', $jwtToken);

        if ($bearer !== 'Bearer') {
            throw new UnauthorizedHttpException('JWT token is invalid');
        }

        return $token;
    }

}
