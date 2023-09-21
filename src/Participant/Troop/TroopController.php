<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TroopController extends AbstractController
{
    public function __construct(
        private readonly TroopService $troopService,
        private readonly TroopLeaderRepository $troopLeaderRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
    ) {
    }

    public function tieParticipantToTroopByLeader(
        Request $request,
        Response $response,
        User $user,
        Event $event
    ): Response {
        $tieCode = $this->getParameterFromBody($request, 'tieCode');

        $troopLeader = $this->troopLeaderRepository->getFromUser($user);
        if ($tieCode === $troopLeader->tieCode) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.cannotTieYourself'));

            return $this->redirect($request, $response, 'getDashboard');
        }

        $troopParticipant = $this->troopParticipantRepository->findTroopParticipantFromTieCode($tieCode, $event);
        if ($troopParticipant === null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.wrongTieCodeForTroopParticipant'));

            return $this->redirect($request, $response, 'getDashboard');
        }
        $this->troopService->tieTroopParticipantToTroopLeader(
            $troopParticipant,
            $troopLeader,
        );

        return $this->redirect(
            $request,
            $response,
            'getDashboard',
        );
    }

    public function tieParticipantToTroopByParticipant(
        Request $request,
        Response $response,
        User $user,
        Event $event,
    ): Response {
        $troopParticipant = $this->troopParticipantRepository->getFromUser($user);
        if ($troopParticipant->troopLeader !== null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.alreadyTied'));

            return $this->redirect($request, $response, 'getDashboard');
        }

        $tieCode = $this->getParameterFromBody($request, 'tieCode');
        $troopLeader = $this->troopLeaderRepository->findTroopLeaderFromTieCode($tieCode, $event);

        if ($troopLeader === null) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.wrongTieCodeForTroopLeader'));

            return $this->redirect($request, $response, 'getDashboard');
        }
        $this->troopService->tieTroopParticipantToTroopLeader(
            $troopParticipant,
            $troopLeader,
        );

        return $this->redirect(
            $request,
            $response,
            'getDashboard',
        );
    }

    public function showParticipant(int $participantId, Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'show-p.twig',
            [
                'pDetail' => $this->troopParticipantRepository->get($participantId),
                'ca' => $user->event->eventType->getContentArbiterTroopParticipant(),
            ]
        );
    }

    public function showUntieParticipant(int $participantId, Response $response): Response
    {
        $patrolParticipant = $this->troopParticipantRepository->get($participantId);

        return $this->view->render($response, 'delete-tp.twig', ['pDetail' => $patrolParticipant]);
    }

    public function untieParticipant(int $participantId, Request $request, Response $response): Response
    {
        $this->troopService->untieTroopParticipant($participantId);
        $this->flashMessages->info($this->translator->trans('flash.info.participantUntied'));

        return $this->redirect(
            $request,
            $response,
            'dashboard',
        );
    }
}
