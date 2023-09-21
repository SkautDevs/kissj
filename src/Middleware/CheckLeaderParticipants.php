<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Slim\Routing\RouteContext;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * participants actions are allowed only for their Patrol Leader or for their Troop Leader
 */
class CheckLeaderParticipants extends BaseMiddleware
{
    public function __construct(
        private readonly PatrolService $patrolService,
        private readonly TroopService $troopService,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new \RuntimeException('Cannot access route in CheckLeaderParticipants middleware');
        }

        $participantId = (int)$route->getArgument('participantId');
        $leader = $this->participantRepository->getParticipantFromUser($this->getUser($request));
        
        if ($leader instanceof PatrolLeader) {
            if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
                $this->patrolService->getPatrolParticipant($participantId),
                $leader,
            )) {
                $this->flashMessages->error($this->translator->trans('flash.error.wrongPatrol'));

                return $this->createRedirectResponse($request, 'dashboard');
            }
        } elseif ($leader instanceof TroopLeader) {
            if (!$this->troopService->troopParticipantBelongsTroopLeader(
                $this->troopParticipantRepository->get($participantId),
                $leader,
            )) {
                $this->flashMessages->error($this->translator->trans('flash.error.wrongTroop'));

                return $this->createRedirectResponse($request, 'dashboard');
            }
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.notLeader'));

            return $this->createRedirectResponse($request, 'dashboard');
        }

        return $handler->handle($request);
    }
}
