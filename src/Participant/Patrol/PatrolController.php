<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantFileService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Skautis\SkautisService;
use kissj\User\User;
use Throwable;
use kissj\User\UserLoginType;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PatrolController extends AbstractController
{
    public function __construct(
        private readonly PatrolService $patrolService,
        private readonly ParticipantService $participantService,
        private readonly ParticipantFileService $participantFileService,
        private readonly PatrolParticipantRepository $patrolParticipantRepository,
        private readonly SkautisService $skautisService,
    ) {
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $closeResult = $this->patrolService->isCloseRegistrationValid($patrolLeader);
        $this->flashRegistrationCloseResult($closeResult);

        if ($closeResult->isValid) {
            return $this->view->render(
                $response,
                'closeRegistration-pl.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]
            );
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $patrolLeader = $this->patrolService->closeRegistration($patrolLeader);

        $patrolLeaderUser = $patrolLeader->getUserButNotNull();
        if ($patrolLeaderUser->status === UserStatus::Closed) {
            $this->flashMessages->success('flash.success.locked');
            $this->logger->info('Locked registration for Patrol Leader with ID '
                . $patrolLeader->id . ', user ID ' . $patrolLeaderUser->id);
        } else {
            $this->flashMessages->error('flash.error.wrongData');
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showAddParticipant(Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        return $this->view->render(
            $response,
            'changeDetails-p.twig',
            [
                'pDetails' => $patrolParticipant,
                'plDetails' => $patrolLeader,
                'ca' => $user->event->eventType->getContentArbiterPatrolParticipant(),
            ]
        );
    }

    public function addParticipant(Request $request, Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;

        /** @var array<string, string> $params */
        $params = $request->getParsedBody();
        $this->participantService->addParamsIntoParticipant($patrolParticipant, $params);

        $ca = $user->event->eventType->getContentArbiterPatrolParticipant();
        $this->participantFileService->handleUploadedFiles($patrolParticipant, $request, $ca->getAllowedItems());

        $this->patrolParticipantRepository->persist($patrolParticipant);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showAddFromSkautis(Request $request, Response $response, User $user): Response
    {
        if ($user->loginType !== UserLoginType::Skautis) {
            $this->flashMessages->error('flash.error.skautisLoginRequired');

            return $this->redirect($request, $response, 'dashboard');
        }

        if (!$this->skautisService->isUserLoggedIn()) {
            $this->flashMessages->error('flash.error.skautisUserNotLoggedIn');

            return $this->redirect($request, $response, 'dashboard');
        }

        try {
            $personId = $this->skautisService->getLeaderPersonId();
            $unitIds = $this->skautisService->getLeaderUnitIds($personId);
            $members = $this->skautisService->getUnitMembers($unitIds);
        } catch (Throwable $e) {
            $this->sentryCollector->collect($e);
            $this->logger->error('Skautis API error while fetching unit members: ' . $e->getMessage());
            $this->flashMessages->error('flash.error.skautisApiError');

            return $this->redirect($request, $response, 'dashboard');
        }

        $patrolLeader = $this->patrolService->getPatrolLeader($user);

        return $this->view->render(
            $response,
            'participant/addFromSkautis.twig',
            [
                'members' => $members,
                'matches' => $this->patrolService->buildMemberMatches($patrolLeader, $members),
            ],
        );
    }

    public function addFromSkautis(Request $request, Response $response, User $user): Response
    {
        if ($user->loginType !== UserLoginType::Skautis || !$this->skautisService->isUserLoggedIn()) {
            $this->flashMessages->error('flash.error.skautisUserNotLoggedIn');

            return $this->redirect($request, $response, 'dashboard');
        }

        /** @var array<string, mixed> $parsedBody */
        $parsedBody = $request->getParsedBody() ?? [];
        /** @var list<string> $selectedIds */
        $selectedIds = $parsedBody['selectedMembers'] ?? [];

        if ($selectedIds === []) {
            $this->flashMessages->warning('flash.warning.noMembersSelected');

            return $this->redirect($request, $response, 'pl-showAddFromSkautis');
        }

        $selectedIdsInt = array_map('intval', $selectedIds);

        try {
            $personId = $this->skautisService->getLeaderPersonId();
            $unitIds = $this->skautisService->getLeaderUnitIds($personId);
            $members = $this->skautisService->getUnitMembers($unitIds);
        } catch (Throwable $e) {
            $this->sentryCollector->collect($e);
            $this->logger->error('Skautis API error while fetching unit members: ' . $e->getMessage());
            $this->flashMessages->error('flash.error.skautisApiError');

            return $this->redirect($request, $response, 'dashboard');
        }

        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $importedCount = $this->patrolService->importParticipantsFromSkautis($patrolLeader, $members, $selectedIdsInt);

        if ($importedCount === 0) {
            $this->flashMessages->warning('flash.warning.noMembersImported');
        } else {
            $this->flashMessages->success('flash.success.skautisImported', [
                '%count%' => (string)$importedCount,
            ]);
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function showChangeDetailsPatrolParticipant(
        int $participantId,
        Response $response,
        ParticipantRepository $participantRepository,
        User $user,
        Event $event,
    ): Response {
        /** @var PatrolParticipant $participant */
        $participant = $participantRepository->getParticipantById($participantId, $event);

        return $this->view->render(
            $response,
            'changeDetails-p.twig',
            [
                'pDetails' => $participant,
                'plDetails' => $participant->patrolLeader,
                'ca' => $user->event->eventType->getContentArbiterPatrolParticipant(),
            ]
        );
    }

    public function changeDetailsPatrolParticipant(
        int $participantId,
        Request $request,
        Response $response
    ): Response {
        /** @var array<string, string> $params */
        $params = $request->getParsedBody();
        /** @var PatrolParticipant $patrolParticipant */
        $patrolParticipant = $this->participantService->addParamsIntoParticipant(
            $this->patrolService->getPatrolParticipant($participantId),
            $params
        );
        $ca = $patrolParticipant->getUserButNotNull()->event->eventType->getContentArbiterPatrolParticipant();
        $this->participantFileService->handleUploadedFiles($patrolParticipant, $request, $ca->getAllowedItems());

        $this->patrolParticipantRepository->persist($patrolParticipant);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect(
            $request,
            $response,
            'dashboard',
        );
    }

    public function showDeleteParticipant(int $participantId, Response $response): Response
    {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render($response, 'delete-p.twig', ['pDetail' => $patrolParticipant]);
    }

    public function deleteParticipant(int $participantId, Request $request, Response $response): Response
    {
        $this->patrolService->deletePatrolParticipant($this->patrolService->getPatrolParticipant($participantId));
        $this->flashMessages->info('flash.info.participantDeleted');

        return $this->redirect(
            $request,
            $response,
            'dashboard',
        );
    }

    public function showParticipant(int $participantId, Response $response, User $user): Response
    {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render(
            $response,
            'show-p.twig',
            [
                'pDetail' => $patrolParticipant,
                'ca' => $user->event->eventType->getContentArbiterPatrolParticipant(),
            ]
        );
    }
}
