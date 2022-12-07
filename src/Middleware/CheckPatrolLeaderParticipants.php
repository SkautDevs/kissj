<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Patrol\PatrolService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Routing\RouteContext;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * participants actions are allowed only for their Patrol Leader
 */
class CheckPatrolLeaderParticipants extends BaseMiddleware
{
    public function __construct(
        private readonly PatrolService $patrolService,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new \RuntimeException('Cannot access route in CheckPatrolLeaderParticipatns middleware');
        }

        $participantId = (int)$route->getArgument('participantId');
        if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
            $this->patrolService->getPatrolParticipant($participantId),
            $this->patrolService->getPatrolLeader($this->getUser($request)),
        )) {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongPatrol'));

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
