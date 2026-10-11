<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\AbstractController;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TechnicalTeamController extends AbstractController
{
    public function __construct(
        private readonly TechnicalTeamService $technicalTeamService,
        private readonly ParticipantService $participantService,
        private readonly TechnicalTeamParticipantRepository $technicalTeamParticipantRepository,
    ) {
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $technicalTeamLeader = $this->technicalTeamService->getTechnicalTeamLeader($user);
        $closeResult = $this->participantService->isCloseRegistrationValid($technicalTeamLeader);
        $this->flashRegistrationCloseResult($closeResult);

        if ($closeResult->isValid) {
            return $this->view->render(
                $response,
                'technicalTeam/closeRegistration-ttl.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl],
            );
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $technicalTeamLeader = $this->technicalTeamService->getTechnicalTeamLeader($user);
        $closeResult = $this->participantService->closeRegistration($technicalTeamLeader);

        if ($closeResult->isValid) {
            $this->flashMessages->success('flash.success.locked');
            $this->logger->info('Locked registration for Technical Team Leader with ID '
                . $technicalTeamLeader->id . ', user ID ' . $technicalTeamLeader->getUserButNotNull()->id);
        } else {
            $this->flashRegistrationCloseResult($closeResult);
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showAddParticipant(Response $response, User $user): Response
    {
        $technicalTeamParticipant = new TechnicalTeamParticipant();
        $technicalTeamParticipant->technicalTeamLeader = $this->technicalTeamService->getTechnicalTeamLeader($user);

        return $this->view->render(
            $response,
            'technicalTeam/changeDetails-ttp.twig',
            [
                'pDetails' => $technicalTeamParticipant,
                'ca' => $user->event->eventType->getContentArbiterTechnicalTeamParticipant(),
            ],
        );
    }

    public function addParticipant(Request $request, Response $response, User $user): Response
    {
        $technicalTeamParticipant = new TechnicalTeamParticipant();
        $technicalTeamParticipant->technicalTeamLeader = $this->technicalTeamService->getTechnicalTeamLeader($user);

        /** @var array<string, string> $params */
        $params = $request->getParsedBody();
        $ca = $user->event->eventType->getContentArbiterTechnicalTeamParticipant();
        $this->participantService->addParamsIntoParticipant($technicalTeamParticipant, $params, $ca->getAllowedItems());

        $this->technicalTeamParticipantRepository->persist($technicalTeamParticipant);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showChangeDetailsParticipant(int $participantId, Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'technicalTeam/changeDetails-ttp.twig',
            [
                'pDetails' => $this->technicalTeamService->getTechnicalTeamParticipant($participantId),
                'ca' => $user->event->eventType->getContentArbiterTechnicalTeamParticipant(),
            ],
        );
    }

    public function changeDetailsParticipant(
        int $participantId,
        Request $request,
        Response $response,
        User $user,
    ): Response {
        /** @var array<string, string> $params */
        $params = $request->getParsedBody();
        $technicalTeamParticipant = $this->technicalTeamService->getTechnicalTeamParticipant($participantId);
        $ca = $user->event->eventType->getContentArbiterTechnicalTeamParticipant();
        $this->participantService->addParamsIntoParticipant(
            $technicalTeamParticipant,
            $params,
            $ca->getAllowedItems(),
        );

        $this->technicalTeamParticipantRepository->persist($technicalTeamParticipant);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showDeleteParticipant(int $participantId, Response $response): Response
    {
        return $this->view->render(
            $response,
            'technicalTeam/delete-ttp.twig',
            ['pDetail' => $this->technicalTeamService->getTechnicalTeamParticipant($participantId)],
        );
    }

    public function deleteParticipant(int $participantId, Request $request, Response $response): Response
    {
        $this->technicalTeamService->deleteTechnicalTeamParticipant(
            $this->technicalTeamService->getTechnicalTeamParticipant($participantId),
        );
        $this->flashMessages->info('flash.info.participantDeleted');

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showParticipant(int $participantId, Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'technicalTeam/show-ttp.twig',
            [
                'pDetail' => $this->technicalTeamService->getTechnicalTeamParticipant($participantId),
                'ca' => $user->event->eventType->getContentArbiterTechnicalTeamParticipant(),
            ],
        );
    }

    public function showChangeDetailsAfterLockParticipant(int $participantId, Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'technicalTeam/changeDetailsAfterLock-ttp.twig',
            [
                'pDetails' => $this->technicalTeamService->getTechnicalTeamParticipant($participantId),
                'ca' => $user->event->eventType->getContentArbiterTechnicalTeamParticipant(),
            ],
        );
    }

    public function changeDetailsAfterLockParticipant(
        int $participantId,
        Request $request,
        Response $response,
        User $user,
    ): Response {
        $participant = $this->technicalTeamService->getTechnicalTeamParticipant($participantId);
        $editableItems = $user->event->eventType->getContentArbiterTechnicalTeamParticipant()->getEditableAfterLockItems();

        /** @var array<string, string|null> $parsed */
        $parsed = $request->getParsedBody();
        $this->participantService->updateEditableAfterLockFields($participant, $parsed, $editableItems);
        $this->logger->info('Changed details after lock for technical team participant ID ' . $participant->id
            . ', user ID ' . $user->id);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'dashboard');
    }
}
