<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
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
        private readonly FioBankPaymentService $bankPaymentService,
        private readonly PatrolService $patrolService,
        private readonly IstService $istService,
        private readonly GuestService $guestService,
        private readonly TroopService $troopService,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly AdminService $adminService,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function showDashboard(Response $response, Event $event, User $user): Response
    {
        return $this->view->render(
            $response,
            'admin/dashboard-admin.twig',
            [
                'patrols' => $this->patrolService->getAllPatrolsStatistics($event, $user),
                'ists' => $this->istService->getAllIstsStatistics($event, $user),
                'troopLeaders' => $this->troopService->getAllTroopLeaderStatistics($event, $user),
                'troopParticipants' => $this->troopService->getAllTroopParticipantStatistics($event, $user),
                'guests' => $this->guestService->getAllGuestsStatistics($event, $user),
            ],
        );
    }

    public function showStats(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);

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
            'caIst' => $event->getEventType()->getContentArbiterIst(),
            'caPl' => $event->getEventType()->getContentArbiterPatrolLeader(),
            'caPp' => $event->getEventType()->getContentArbiterPatrolParticipant(),
            'caTl' => $event->getEventType()->getContentArbiterTroopLeader(),
            'caTp' => $event->getEventType()->getContentArbiterTroopParticipant(),
            'caGuest' => $event->getEventType()->getContentArbiterGuest(),
        ]);
    }

    public function showOpen(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);

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
            'caIst' => $event->getEventType()->getContentArbiterIst(),
            'caPl' => $event->getEventType()->getContentArbiterPatrolLeader(),
            'caPp' => $event->getEventType()->getContentArbiterPatrolParticipant(),
            'caTl' => $event->getEventType()->getContentArbiterTroopLeader(),
            'caTp' => $event->getEventType()->getContentArbiterTroopParticipant(),
            'caGuest' => $event->getEventType()->getContentArbiterGuest(),
        ]);
    }

    public function showApproving(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        $orderByUpdatedAtDesc = new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC);

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
            'caIst' => $event->getEventType()->getContentArbiterIst(),
            'caPl' => $event->getEventType()->getContentArbiterPatrolLeader(),
            'caPp' => $event->getEventType()->getContentArbiterPatrolParticipant(),
            'caTl' => $event->getEventType()->getContentArbiterTroopLeader(),
            'caTp' => $event->getEventType()->getContentArbiterTroopParticipant(),
            'caGuest' => $event->getEventType()->getContentArbiterGuest(),
        ]);
    }

    public function approveParticipant(int $participantId, Request $request, Response $response): Response
    {
        $participant = $this->participantRepository->get($participantId);

        $this->participantService->approveRegistration($participant);
        $this->logger->info('Approved registration for participant with ID ' . $participant->id);

        return $this->redirect($request, $response, 'admin-show-approving');
    }

    public function showDenyParticipant(int $participantId, Response $response): Response
    {
        $participant = $this->participantRepository->get($participantId);

        return $this->view->render($response, 'admin/deny-admin.twig', ['participant' => $participant]);
    }

    public function denyParticipant(int $participantId, Request $request, Response $response): Response
    {
        // TODO check if correct event
        $reason = $this->getParameterFromBody($request, 'reason', true);
        $participant = $this->participantRepository->get($participantId);
        $this->participantService->denyRegistration($participant, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.denied'));
        $this->logger->info('Denied registration for participant with ID '
            . $participantId . ' and role ' . $participant->role?->value . ' with reason: ' . $reason);

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

    public function showCancelPayment(int $paymentId, Response $response): Response
    {
        $payment = $this->paymentRepository->get($paymentId);

        return $this->view->render($response, 'admin/cancelPayment-admin.twig', ['payment' => $payment]);
    }

    public function cancelPayment(int $paymentId, User $user, Request $request, Response $response): Response
    {
        $reason = $this->getParameterFromBody($request, 'reason', true);
        $payment = $this->paymentRepository->get($paymentId);
        if ($payment->participant->getUserButNotNull()->event->id !== $user->event->id) {
            $this->flashMessages->warning($this->translator->trans('flash.error.confirmNotAllowed'));
            $this->logger->info('Payment ID ' . $paymentId
                . ' cannot be confirmed from admin with event id ' . $user->event->id);
        } else {
            $this->participantService->cancelPayment($payment, $reason);
            $this->flashMessages->info($this->translator->trans('flash.info.paymentCanceled'));
            $this->logger->info('Cancelled payment ID ' . $paymentId . ' for participant with reason: ' . $reason);
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function cancelAllDuePayments(Request $request, Response $response, User $user): Response
    {
        $this->paymentService->cancelDuePayments($user->event);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function confirmPayment(int $paymentId, User $user, Request $request, Response $response): Response
    {
        $payment = $this->paymentRepository->get($paymentId);
        $participant = $payment->participant;
        if ($participant->getUserButNotNull()->event->id !== $user->event->id) {
            $this->flashMessages->warning($this->translator->trans('flash.error.confirmNotAllowed'));
            $this->logger->info('Payment ID ' . $paymentId
                . ' cannot be confirmed from admin with event id ' . $user->event->id);
        } else {
            $this->paymentService->confirmPayment($payment);
            $this->flashMessages->success($this->translator->trans('flash.success.confirmPayment'));
            $this->logger->info('Payment ID ' . $paymentId . ' manually confirmed as paid');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function showFile(string $filename): Response
    {
        // TODO check if correct event
        $file = $this->fileHandler->getFile($filename);
        $response = new \Slim\Psr7\Response(200, null, $file->stream);
        $response = $response->withAddedHeader('Content-Type', $file->mimeContentType);

        return $response;
    }

    public function showAutoPayments(Response $response, Event $event): Response
    {
        $arguments = [
            'bankPayments' => $this->bankPaymentRepository->getAllBankPaymentsOrdered($event),
            'bankPaymentsTodo' => $this->bankPaymentRepository->getBankPaymentsOrderedWithStatus(
                $event,
                BankPayment::STATUS_UNKNOWN,
            ),
        ];

        return $this->view->render($response, 'admin/paymentsAuto-admin.twig', $arguments);
    }

    public function setBreakpointFromRoute(Request $request, Response $response, Event $event): Response
    {
        $result = $this->bankPaymentService->setBreakpoint(DateTimeUtils::getDateTime('2022-01-01'), $event);

        if ($result) {
            $this->flashMessages->success('Set breakpoint successfully');
        } else {
            $this->flashMessages->error('Something gone wrong, probably unvalid token :(');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function updatePayments(Request $request, Response $response, Event $event): Response
    {
        $this->paymentService->updatePayments($event);

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function markBankPaymentPaired(Request $request, Response $response, int $paymentId): Response
    {
        // TODO check if correct event
        $notice = $this->getParameterFromBody($request, 'notice', true);
        $this->bankPaymentService->setBankPaymentPaired($paymentId);
        $this->logger->info('Payment with ID ' . $paymentId . ' has been marked as paired with notice: ' . $notice);
        $this->flashMessages->info($this->translator->trans('flash.info.markedAsPaired'));

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function markBankPaymentUnrelated(Request $request, Response $response, int $paymentId): Response
    {
        $this->bankPaymentService->setBankPaymentUnrelated($paymentId);
        $this->logger->info('Payment with ID ' . $paymentId . ' has been marked as unrelated');
        $this->flashMessages->info($this->translator->trans('flash.info.markedAsUnrelated'));

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
        );
    }

    public function showTransferPayment(Request $request, Response $response, Event $event): Response
    {
        $emailFrom = $request->getQueryParams()['emailFrom'];
        $emailTo = $request->getQueryParams()['emailTo'];

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
                $this->flashMessages
            ),
        ]);
    }

    public function transferPayment(Request $request, Response $response, Event $event): Response
    {
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
            throw new \RuntimeException('Cannot do transfer');
        }

        $this->adminService->transferPayment($participantFrom, $participantTo);
        $this->flashMessages->success($this->translator->trans('flash.success.transfer'));

        return $this->redirect(
            $request,
            $response,
            'admin-dashboard',
        );
    }

    public function changeAdminNote(Request $request, Response $response, int $participantId): Response
    {
        $participant = $this->participantRepository->get($participantId);
        // TODO check if participant is from correct event
        $participant->adminNote = $this->getParameterFromBody($request, 'adminNote');
        $this->participantRepository->persist($participant);

        return $this->getResponseWithJson($response, ['adminNote' => $participant->adminNote]);
    }

    public function showParticipantDetails(Response $response, int $participantId, Event $event): Response
    {
        $participant = $this->participantRepository->get($participantId);
        // TODO check if correct event

        return $this->view->render(
            $response,
            'admin/changeParticipantDetails.twig',
            [
                'person' => $participant,
                'ca' => $this->participantService->getContentArbiterForParticipant($participant),
                'caPp' => $event->getEventType()->getContentArbiterPatrolParticipant(),
                'caTp' => $event->getEventType()->getContentArbiterTroopParticipant(),
            ],
        );
    }

    public function changeParticipantDetails(Request $request, Response $response, int $participantId): Response
    {
        $participant = $this->participantRepository->get($participantId);

        /** @var string[] $parsed */
        $parsed = $request->getParsedBody();
        $this->participantService->addParamsIntoParticipant($participant, $parsed);
        $this->participantService->handleUploadedFiles($participant, $request);

        $this->participantRepository->persist($participant);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));
        // TODO log participant edit

        return $this->redirect(
            $request,
            $response,
            'admin-show-stats',
        );
    }
    
    public function showRole(Response $response, int $participantId, Event $event): Response
    {
        $participant = $this->participantRepository->get($participantId);

        return $this->view->render($response, 'admin/changeRole.twig', [
            'person' => $participant,
            'roles' => $event->getAvailableRoles(),
        ]);
    }
    
    public function changeRole(Request $request, Response $response, int $participantId, Event $event): Response
    {
        $participant = $this->participantRepository->get($participantId);
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

    public function generateMorePayments(Request $request, Response $response, Event $event): Response
    {
        if ($event->getEventType()->isMultiplePaymentsAllowed() === false) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.multiplePaymentsNotAllowed'));
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

    public function showTroopManagement(Request $request, Response $response, Event $event): Response
    {
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

    public function tieTogether(Request $request, Response $response, Event $event): Response
    {
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

    public function untie(Request $request, Response $response, Event $event): Response
    {
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
}
