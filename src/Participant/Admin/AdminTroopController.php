<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Participant\Troop\TroopParticipant;
use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminTroopController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly TroopService $troopService,
    ) {
    }

    public function showTroopManagement(
        Response $response,
        Event $event,
    ): Response {
        return $this->view->render($response, 'admin/troopManagement.twig', [
            'troopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopLeader],
                [UserStatus::Open],
                $event,
            ),
            'troopParticipants' => $this->troopParticipantRepository->getAllWithoutTroop(
                $event,
            ),
            'caTl' => $event->getEventType()->getContentArbiterTroopLeader(),
            'caTp' => $event->getEventType()->getContentArbiterTroopParticipant(),
        ]);
    }

    public function tieTogether(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $troopLeaderCode = $this->getParameterFromBody($request, 'tieCodeLeader');
        $troopParticipantCode = $this->getParameterFromBody($request, 'tieCodeParticipant');

        $this->troopService->tryTieTogetherWithMessages(
            $troopLeaderCode,
            $troopParticipantCode,
            $event,
        );

        return $this->redirect(
            $request,
            $response,
            'admin-troop-management',
        );
    }

    public function untie(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $troopParticipantCode = $this->getParameterFromBody($request, 'tieCodeParticipant');

        $this->troopService->tryUntieWithMessages(
            $troopParticipantCode,
            $event,
        );

        return $this->redirect(
            $request,
            $response,
            'admin-troop-management',
        );
    }

    public function swapTroopLeader(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        if ($participant instanceof TroopParticipant === false || $participant->troopLeader === null) {
            $this->flashMessages->warning('flash.warning.troopParticipantNotInTroop');

            return $this->redirect($request, $response, 'admin-mend-participant', ['participantId' => (string) $participantId]);
        }

        $this->troopService->swapTroopLeaderWithParticipant($participant);

        return $this->redirect($request, $response, 'admin-mend-participant', ['participantId' => (string) $participantId]);
    }
}
