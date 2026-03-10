<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Routing\RouteContext;

class ParticipantOwnerOrAdminMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $filename = $route?->getArgument('filename');
        if ($filename === null) {
            throw new \RuntimeException('Missing filename route argument');
        }

        $user = $this->getUser($request);
        $participant = $this->participantRepository->findParticipantByUploadedFilename($filename, $user->event);
        if ($participant === null) {
            $this->flashMessages->error('flash.error.fileNotFound');

            return $this->createRedirectResponse($request, 'getDashboard');
        }

        $isOwner = $participant->getUserButNotNull()->id === $user->id;
        if ($isOwner || $user->isAdmin()) {
            $request = $request->withAttribute('participant', $participant);

            return $handler->handle($request);
        }

        $this->flashMessages->error('flash.error.fileNotFound');

        return $this->createRedirectResponse($request, 'getDashboard');
    }
}
