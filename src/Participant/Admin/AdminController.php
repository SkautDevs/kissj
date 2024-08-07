<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Participant\Patrol\PatrolLeader;
use RuntimeException;
use kissj\AbstractController;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\Event;
use kissj\FileHandler\UploadFileHandler;
use kissj\Import\ImportSrs;
use kissj\Orm\Order;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PaymentService $paymentService,
        private readonly PaymentRepository $paymentRepository,
        private readonly BankPaymentRepository $bankPaymentRepository,
        private readonly TroopService $troopService,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly AdminService $adminService,
        private readonly UserRepository $userRepository,
        private readonly ImportSrs $importSrs,
        private readonly UploadFileHandler $uploadFileHandler,
    ) {
    }

    public function showDashboard(
        Response $response,
        Event $event,
    ): Response {
        $eventType = $event->getEventType();
        $contingentStatistic = [];
        if ($eventType->showContingentPatrolStats()) {
            $contingentStatistic = $this->participantRepository->getContingentStatistic(
                $event,
                ParticipantRole::PatrolLeader,
                $eventType->getContingents(),
            );
        }

        $istArrivalStatistic = [];
        if ($eventType->getContentArbiterIst()->arrivalDate) {
            $istArrivalStatistic = $this->participantRepository->getIstArrivalStatistic($event);
        }

        $foodStatistic = [];
        if ($eventType->showFoodStats()) {
            $foodStatistic = $this->participantRepository->getFoodStatistic($event);
        }

        return $this->view->render(
            $response,
            'admin/dashboard-admin.twig',
            [
                'patrols' => $this->participantRepository->getStatistic($event, ParticipantRole::PatrolLeader),
                'ists' => $this->participantRepository->getStatistic($event, ParticipantRole::Ist),
                'troopLeaders' => $this->participantRepository->getStatistic($event, ParticipantRole::TroopLeader),
                'troopParticipants' => $this->participantRepository->getStatistic($event, ParticipantRole::TroopParticipant),
                'guests' => $this->participantRepository->getStatistic($event, ParticipantRole::Guest),
                'contingentsPatrolStatistic' => $contingentStatistic,
                'istArrivalStatistic' => $istArrivalStatistic,
                'foodStatistic' => $foodStatistic,
                'entryStatistic' => $this->participantRepository->getEntryStatistic($event),
            ],
        );
    }

    public function showPaid(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);
        $eventType = $event->getEventType();

        return $this->view->render($response, 'admin/stats-admin.twig', [
            'paidPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::PatrolLeader],
                [UserStatus::Paid],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'paidTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopLeader],
                [UserStatus::Paid],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'paidTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopParticipant],
                [UserStatus::Paid],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'paidIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Ist],
                [UserStatus::Paid],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'paidGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Guest],
                [UserStatus::Paid],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'caIst' => $eventType->getContentArbiterIst(),
            'caPl' => $eventType->getContentArbiterPatrolLeader(),
            'caPp' => $eventType->getContentArbiterPatrolParticipant(),
            'caTl' => $eventType->getContentArbiterTroopLeader(),
            'caTp' => $eventType->getContentArbiterTroopParticipant(),
            'caGuest' => $eventType->getContentArbiterGuest(),
        ]);
    }

    public function showOpen(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);
        $eventType = $event->getEventType();

        return $this->view->render($response, 'admin/open-admin.twig', [
            'openPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::PatrolLeader],
                [UserStatus::Open],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
                true,
            ),
            'openTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopLeader],
                [UserStatus::Open],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
                true,
            ),
            'openTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopParticipant],
                [UserStatus::Open],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
                true,
            ),
            'openIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Ist],
                [UserStatus::Open],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
                true,
            ),
            'openGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Guest],
                [UserStatus::Open],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
                true,
            ),
            'caIst' => $eventType->getContentArbiterIst(),
            'caPl' => $eventType->getContentArbiterPatrolLeader(),
            'caPp' => $eventType->getContentArbiterPatrolParticipant(),
            'caTl' => $eventType->getContentArbiterTroopLeader(),
            'caTp' => $eventType->getContentArbiterTroopParticipant(),
            'caGuest' => $eventType->getContentArbiterGuest(),
        ]);
    }

    public function showApproving(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);
        $eventType = $event->getEventType();

        return $this->view->render($response, 'admin/approve-admin.twig', [
            'closedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::PatrolLeader],
                [UserStatus::Closed],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'closedTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopLeader],
                [UserStatus::Closed],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'closedTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopParticipant],
                [UserStatus::Closed],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'closedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Ist],
                [UserStatus::Closed],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'closedGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Guest],
                [UserStatus::Closed],
                $event,
                $user,
                [$orderByUpdatedAtDesc],
            ),
            'caIst' => $eventType->getContentArbiterIst(),
            'caPl' => $eventType->getContentArbiterPatrolLeader(),
            'caPp' => $eventType->getContentArbiterPatrolParticipant(),
            'caTl' => $eventType->getContentArbiterTroopLeader(),
            'caTp' => $eventType->getContentArbiterTroopParticipant(),
            'caGuest' => $eventType->getContentArbiterGuest(),
        ]);
    }

    public function approveParticipant(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        $this->participantService->approveRegistration($participant);
        $this->logger->info('Approved registration for participant with ID ' . $participant->id);

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
            . $participantId . ' and role ' . ($participant->role?->value ?? 'missing') . ' with reason: ' . $reason);

        return $this->redirect($request, $response, 'admin-show-approving');
    }

    public function showPayments(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/payments-admin.twig', [
            'approvedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::Ist],
                [UserStatus::Approved],
                $event,
                $user,
            ),
            'approvedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::PatrolLeader],
                [UserStatus::Approved],
                $event,
                $user,
            ),
            'approvedTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [ParticipantRole::TroopLeader],
                [UserStatus::Approved],
                $event,
                $user,
            ),
        ]);
    }

    public function showCancelPayment(
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $payment = $this->paymentRepository->getById($paymentId, $event);

        return $this->view->render($response, 'admin/cancelPayment-admin.twig', ['payment' => $payment]);
    }

    public function cancelPayment(
        Request $request,
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $reason = $this->getParameterFromBody($request, 'reason', true);
        $payment = $this->paymentRepository->getById($paymentId, $event);

        $this->participantService->cancelPayment($payment, $reason);
        $this->flashMessages->info('flash.info.paymentCanceled');
        $this->logger->info('Cancelled payment ID ' . $paymentId . ' for participant with reason: ' . $reason);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function cancelAllDuePayments(
        Request $request,
        Response $response,
        User $user,
    ): Response {
        $this->paymentService->cancelDuePayments($user->event);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function confirmPayment(
        Request $request,
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $payment = $this->paymentRepository->getById($paymentId, $event);
        $participant = $payment->participant;

        $this->paymentService->confirmPayment($payment);
        $this->flashMessages->success('flash.success.confirmPayment');
        $this->logger->info('Payment ID ' . $paymentId . ' manually confirmed as paid');

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function showFile(
        string $filename,
    ): Response {
        // TODO check if correct event
        $file = $this->fileHandler->getFile($filename);
        $response = new \Slim\Psr7\Response(200, null, $file->stream);
        $response = $response->withAddedHeader('Content-Type', $file->mimeContentType);

        return $response;
    }

    public function showAutoPayments(
        Response $response,
        Event $event,
    ): Response {
        $arguments = [
            'bankPayments' => $this->bankPaymentRepository->getAllBankPaymentsOrdered($event),
            'bankPaymentsTodo' => $this->bankPaymentRepository->getBankPaymentsOrderedWithStatus(
                $event,
                BankPayment::STATUS_UNKNOWN,
            ),
        ];

        return $this->view->render($response, 'admin/paymentsAuto-admin.twig', $arguments);
    }

    public function updatePayments(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $this->paymentService->updatePayments($event);

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function markBankPaymentPaired(
        Request $request,
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        // TODO check if correct event
        $notice = $this->getParameterFromBody($request, 'notice', true);
        $this->paymentService->setBankPaymentPaired($paymentId);
        $this->logger->info('Payment with ID ' . $paymentId . ' has been marked as paired with notice: ' . $notice);
        $this->flashMessages->info('flash.info.markedAsPaired');

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function markBankPaymentUnrelated(
        Request $request,
        Response $response,
        int $paymentId,
    ): Response {
        $this->paymentService->setBankPaymentUnrelated($paymentId);
        $this->logger->info('Payment with ID ' . $paymentId . ' has been marked as unrelated');
        $this->flashMessages->info('flash.info.markedAsUnrelated');

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function showTransferPayment(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $emailFrom = $this->getParameterFromQuery($request, 'emailFrom');
        $emailTo = $this->getParameterFromQuery($request, 'emailTo');

        $userFrom = $this->userRepository->findUserFromEmail($emailFrom, $event);
        $userTo = $this->userRepository->findUserFromEmail($emailTo, $event);

        $participantFrom = $this->participantRepository->findParticipantFromUser($userFrom);
        $participantTo = $this->participantRepository->findParticipantFromUser($userTo);

        return $this->view->render($response, 'admin/transferPayment-admin.twig', [
            'emailFrom' => $emailFrom,
            'emailTo' => $emailTo,
            'from' => $participantFrom,
            'to' => $participantTo,
            'transferPossible' => $this->adminService->isPaymentTransferPossible(
                $participantFrom,
                $participantTo,
                $this->flashMessages,
            ),
        ]);
    }

    public function transferPayment(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $emailFrom = $this->getParameterFromBody($request, 'emailFrom');
        $emailTo = $this->getParameterFromBody($request, 'emailTo');

        $userFrom = $this->userRepository->getUserFromEmail($emailFrom, $event);
        $userTo = $this->userRepository->getUserFromEmail($emailTo, $event);

        $participantFrom = $this->participantRepository->getParticipantFromUser($userFrom);
        $participantTo = $this->participantRepository->getParticipantFromUser($userTo);

        if (!$this->adminService->isPaymentTransferPossible(
            $participantFrom,
            $participantTo,
            $this->flashMessages,
        )) {
            $this->flashMessages->error('flash.error.transferFailed');
            $this->sentryCollector->collect(new \RuntimeException(sprintf(
                'Cannot do transfer from user ID: %s to user ID: %s',
                $userFrom->id,
                $userTo->id,
            )));
        } else {
            $this->adminService->transferPayment($participantFrom, $participantTo);
            $this->flashMessages->success('flash.success.transfer');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-dashboard',
        );
    }

    public function changeAdminNote(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        $this->participantService->setAdminNote(
            $participant,
            $this->getParameterFromBody($request, 'adminNote'),
        );

        return $this->getResponseWithJson($response, ['adminNote' => $participant->adminNote]);
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
				'ca' => $this->participantService->getContentArbiterForParticipant($participant),
			],
        );
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
                'ca' => $this->participantService->getContentArbiterForParticipant($participant),
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

        /** @var string[] $parsed */
        $parsed = $request->getParsedBody();
        $this->participantService->addParamsIntoParticipant($participant, $parsed);
        $this->participantService->handleUploadedFiles($participant, $request);

        $this->participantRepository->persist($participant);
        $this->flashMessages->success('flash.success.detailsSaved');
        $this->logger->info('Participant with ID ' . $participantId . ' details changed by user with ID ' . $user->id);

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            [
				'participantId' => (string)$participantId,
			],
        );
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

        $success = $this->participantService->tryChangeRoleWithMessages($roleFromBody, $participant, $event);

        if ($success) {
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

    public function generateMorePayments(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        if ($event->getEventType()->isMultiplePaymentsAllowed() === false) {
            $this->flashMessages->warning('flash.warning.multiplePaymentsNotAllowed');
        } else {
            $participants = $this->participantRepository->getPaidParticipantsWithExactPayments($event, 1, 10);
            $count = $this->participantService->generatePaymentsFor($participants);

            $this->flashMessages->info($this->translator->trans('flash.info.generatedPayments') . ': ' . $count);
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-stats',
        );
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

    public function importIstFromSrs(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $uploadedFile = $this->uploadFileHandler->resolveUploadedFile($request);
        if ($uploadedFile === null) {
            $this->flashMessages->warning('flash.warning.importCantStart');

            return $this->redirect(
                $request,
                $response,
                'admin-dashboard',
            );
        }

        $this->importSrs->importIst($uploadedFile, $event);

        return $this->redirect(
            $request,
            $response,
            'admin-dashboard',
        );
    }
}
