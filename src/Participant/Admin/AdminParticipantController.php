<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Participant\ParticipantException;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopParticipant;
use RuntimeException;
use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantFileService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\RoleChangeResult;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminParticipantController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
        private readonly ParticipantFileService $participantFileService,
        private readonly AdminService $adminService,
        private readonly Metrics $metrics,
    ) {
    }

    public function showPaid(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/stats-admin.twig', [
            'sections' => $this->adminService->getParticipantSections(
                $event,
                $user,
                UserStatus::Paid,
                ['ist' => null, 'pl' => null, 'tl' => null, 'guest' => null, 'ot' => null],
                orderByUpdatedAt: true,
            ),
        ]);
    }

    public function showOpen(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/open-admin.twig', [
            'sections' => $this->adminService->getParticipantSections(
                $event,
                $user,
                UserStatus::Open,
                ['ist' => null, 'pl' => null, 'tl' => null, 'tp' => null, 'guest' => null, 'ot' => null],
                orderByUpdatedAt: true,
                filterEmpty: true,
            ),
        ]);
    }

    public function showApproving(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/approve-admin.twig', [
            'sections' => $this->adminService->getParticipantSections(
                $event,
                $user,
                UserStatus::Closed,
                ['ist' => null, 'pl' => null, 'tl' => null, 'tp' => null, 'guest' => null, 'ot' => null],
                orderByUpdatedAt: true,
            ),
        ]);
    }

    public function approveParticipant(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        try {
            $participant = $this->participantService->approveRegistration($participant);

            if ($participant instanceof TroopParticipant) {
                $this->flashMessages->success('flash.success.tpApproved');
            } elseif ($participant->getUserButNotNull()->status === UserStatus::Paid) {
                $this->flashMessages->success('flash.success.approvedWithoutPayment');
            } else {
                $this->flashMessages->success('flash.success.approved');
            }
        } catch (ParticipantException $e) {
            $this->flashMessages->warning($e->translationKey);
        }

        return $this->redirect($request, $response, 'admin-show-approving');
    }

    public function showDenyParticipant(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        return $this->view->render($response, 'admin/deny-admin.twig', ['participant' => $participant]);
    }

    public function denyParticipant(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $reason = $this->getParameterFromBody($request, 'reason', true);
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        $this->participantService->denyRegistration($participant, $reason);

        $this->flashMessages->info('flash.info.denied');
        $this->logger->info('Denied registration for participant with ID '
            . $participantId . ' and role ' . ($participant->role->value ?? 'missing') . ' with reason: ' . $reason);
        $this->metrics->count(MetricName::RegistrationsDenied, 1, ['role' => $participant->role->value ?? 'unknown role']);

        return $this->redirect($request, $response, 'admin-show-approving');
    }

    public function showParticipantDetails(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $eventType = $event->getEventType();

        return $this->view->render(
            $response,
            'admin/changeParticipantDetails.twig',
            [
                'person' => $participant,
                'ca' => $event->eventType->getContentArbiterForRole($participant->getRoleOrFail()),
                'caPp' => $eventType->getContentArbiterPatrolParticipant(),
                'caTp' => $eventType->getContentArbiterTroopParticipant(),
            ],
        );
    }

    public function changeParticipantDetails(
        Request $request,
        Response $response,
        Event $event,
        User $user,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        /** @var array<string, string|null> $parsed */
        $parsed = $request->getParsedBody();
        $ca = $event->eventType->getContentArbiterForRole($participant->getRoleOrFail());
        $this->participantService->addParamsIntoParticipant($participant, $parsed);
        $this->participantFileService->handleUploadedFiles($participant, $request, $ca->getAllowedItems());

        $this->participantRepository->persist($participant);
        $this->flashMessages->success('flash.success.detailsSaved');
        $this->logger->info('Participant with ID ' . $participantId . ' details changed by user with ID ' . $user->id);
        $this->metrics->count(MetricName::ParticipantsAdminEdited, 1);

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
    }

    public function changeAdminValues(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        $subcamp = trim($this->getParameterFromBody($request, 'subcamp'));
        $internalUniqueId = trim($this->getParameterFromBody($request, 'internalUniqueId'));
        $internalCommonId = trim($this->getParameterFromBody($request, 'internalCommonId'));

        $result = $this->adminService->setAdminValues(
            $participant,
            $event,
            $subcamp === '' ? null : $subcamp,
            $internalUniqueId === '' ? null : $internalUniqueId,
            $internalCommonId === '' ? null : $internalCommonId,
        );

        match ($result) {
            AdminValuesSaveResult::Saved => $this->flashMessages->success('flash.success.adminValuesSaved'),
            AdminValuesSaveResult::UnknownSubcamp => $this->flashMessages->error('flash.error.adminValuesUnknownSubcamp'),
            AdminValuesSaveResult::UniqueIdTaken => $this->flashMessages->error('flash.error.adminValuesUniqueIdTaken'),
        };

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
    }

    public function showAdminValuesImport(Response $response): Response
    {
        return $this->renderAdminValuesImport($response, null, '', false);
    }

    public function importAdminValues(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $csv = $this->getParameterFromBody($request, 'csv');
        $apply = $this->getParameterFromBody($request, 'action') === 'apply';

        $report = $this->adminService->importAdminValues($event, $csv, $apply);

        if ($apply && $report->okCount > 0) {
            $this->flashMessages->success('flash.success.adminValuesImported');
        }

        return $this->renderAdminValuesImport($response, $report, $csv, $apply);
    }

    public function showRole(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        return $this->view->render($response, 'admin/changeRole.twig', [
            'person' => $participant,
            'roles' => $event->getAvailableRoles(),
        ]);
    }

    public function changeRole(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $roleFromBody = $this->getParameterFromBody($request, 'role');

        $roleChangeResult = $this->participantService->tryChangeRole($roleFromBody, $participant, $event);
        match ($roleChangeResult) {
            RoleChangeResult::Success => $this->flashMessages->success($roleChangeResult->value),
            RoleChangeResult::RoleNotValid => $this->flashMessages->error($roleChangeResult->value),
            RoleChangeResult::PatrolHasParticipants,
            RoleChangeResult::TroopHasParticipants,
            RoleChangeResult::SameRole,
            RoleChangeResult::NotOpen => $this->flashMessages->warning($roleChangeResult->value),
        };

        if ($roleChangeResult === RoleChangeResult::Success) {
            return $this->redirect(
                $request,
                $response,
                'admin-show-open',
            );
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-role',
            ['participantId' => (string)$participantId],
        );
    }

    public function cancel(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->findParticipantById($participantId, $event);

        if ($participant instanceof PatrolLeader) {
            $this->flashMessages->warning('flash.warning.cancelPatrolLeaderNotSupported');
        } elseif ($participant instanceof PatrolParticipant) {
            // a patrol participant has no own user - cancelling would flip the patrol leader's
            // shared user to cancelled and hide the whole patrol from the entry app
            $this->flashMessages->warning('flash.warning.cancelPatrolParticipantNotSupported');
        } elseif ($participant instanceof Participant) {
            $this->participantService->cancelParticipant($participant);
            $this->flashMessages->success('flash.success.participantCancelled');
        } else {
            $this->flashMessages->error('flash.error.participantNotCancelled');
            $this->sentryCollector->collect(
                new RuntimeException('Participant with ID ' . $participantId . ' during cancellation not found'),
            );
        }

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
    }

    public function uncancel(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        if ($participant->getUserButNotNull()->status !== UserStatus::Cancelled) {
            $this->flashMessages->warning('flash.warning.participantNotInCancelledStatus');
        } elseif ($participant instanceof PatrolLeader) {
            $this->flashMessages->warning('flash.warning.cancelPatrolLeaderNotSupported');
        } else {
            $this->participantService->uncancelParticipant($participant);
            $this->flashMessages->success('flash.success.participantUncancelled');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
    }

    public function mendParticipant(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        return $this->view->render(
            $response,
            'admin/mendParticipant.twig',
            [
				'participant' => $participant,
				'ca' => $event->eventType->getContentArbiterForRole($participant->getRoleOrFail()),
			],
        );
    }

    private function renderAdminValuesImport(
        Response $response,
        ?AdminValuesImportReport $report,
        string $csv,
        bool $applied,
    ): Response {
        return $this->view->render(
            $response,
            'admin/adminValuesImport-admin.twig',
            [
                'report' => $report,
                'csv' => $csv,
                'applied' => $applied,
                'expectedHeader' => implode(',', AdminService::IMPORT_HEADER),
            ],
        );
    }
}
