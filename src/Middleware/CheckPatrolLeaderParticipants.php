<?php

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
class CheckPatrolLeaderParticipants extends BaseMiddleware {
    private PatrolService $patrolService;
    private FlashMessagesInterface $flashMessages;
    private TranslatorInterface $translator;

    public function __construct(
        PatrolService $patrolService,
        FlashMessagesInterface $flashMessages,
        TranslatorInterface $translator
    ) {
        $this->patrolService = $patrolService;
        $this->flashMessages = $flashMessages;
        $this->translator = $translator;
    }

    public function process(Request $request, ResponseHandler $handler): Response {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new \RuntimeException('Cannot access route in CheckPatrolLeaderParticipatns middleware');
        }
        $participantId = $route->getArgument('participantId');
        if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
            $this->patrolService->getPatrolParticipant($participantId),
            $this->patrolService->getPatrolLeader($request->getAttribute('user')))) {

            $this->flashMessages->error($this->translator->trans('flash.error.wrongPatrol'));

            $url = $this->getRouter($request)->urlFor('pl-dashboard');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
