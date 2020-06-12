<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Patrol\PatrolService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * participants actions are allowed only for their Patrol Leader
 */
class CheckPatrolLeaderParticipants extends BaseMiddleware {
    private $patrolService;
    private $flashMessages;
    private $translator;

    public function __construct(
        PatrolService $patrolService,
        FlashMessagesInterface $flashMessages,
        TranslatorInterface $translator
    ) {
        $this->patrolService = $patrolService;
        $this->flashMessages = $flashMessages;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $route = \Slim\Routing\RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new \RuntimeException('None route found');
        }
        $route->getArgument('participantId');
        $routeParams = $request->getAttribute('routeInfo')[2]; // get route params from request (undocumented feature) // TODO fix as in v4
        if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
            $this->patrolService->getPatrolParticipant($routeParams['participantId']),
            $this->patrolService->getPatrolLeader($request->getAttribute('user')))) {

            $this->flashMessages->error($this->translator->trans('flash.error.wrongPatrol'));

            $url = $this->getRouter($request)->urlFor('pl-dashboard');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
