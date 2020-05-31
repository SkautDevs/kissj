<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Patrol\PatrolService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * participants actions are allowed only for their Patrol Leader
 */
class CheckPatrolLeaderParticipants extends AbstractMiddleware {
    private $flashMessages;
    private $patrolService;

    public function __construct(FlashMessagesInterface $flashMessages, PatrolService $patrolService) {
        $this->flashMessages = $flashMessages;
        $this->patrolService = $patrolService;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $this->process($request, $handler);
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

            $this->flashMessages->error('Pardon, but you cannot edit or view participants outside your patrol.');

            $url = $this->getRouter($request)->urlFor('pl-dashboard');
            $response = new \Slim\Psr7\Response();

            return $response->withHeader('Location', $url)->withStatus(302);
        }

        return $handler->handle($request);
    }
}
