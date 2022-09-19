<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use DateTimeImmutable;
use kissj\AbstractController;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstService;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
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
        private ParticipantService $participantService,
        private ParticipantRepository $participantRepository,
        private PaymentService $paymentService,
        private PaymentRepository $paymentRepository,
        private BankPaymentRepository $bankPaymentRepository,
        private FioBankPaymentService $bankPaymentService,
        private PatrolService $patrolService,
        private IstService $istService,
        private GuestService $guestService,
        private TroopService $troopService,
        private AdminService $adminService,
        private UserRepository $userRepository,
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
        $orderByUpdatedAtDesc = new Order(Order::FILED_UPDATED_AT, Order::DIRECTION_DESC);

        return $this->view->render($response, 'admin/stats-admin.twig', [
            'paidPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [UserStatus::Paid],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'paidTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_LEADER],
                [UserStatus::Paid],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'paidTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_PARTICIPANT],
                [UserStatus::Paid],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'paidIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [UserStatus::Paid],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'paidGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_GUEST],
                [UserStatus::Paid],
                $event,
                $user,
                $orderByUpdatedAtDesc,
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
        $orderByUpdatedAtDesc = new Order(Order::FILED_UPDATED_AT, Order::DIRECTION_DESC);

        return $this->view->render($response, 'admin/open-admin.twig', [
            'openPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [UserStatus::Open],
                $event,
                $user,
                $orderByUpdatedAtDesc,
                true,
            ),
            'openTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_LEADER],
                [UserStatus::Open],
                $event,
                $user,
                $orderByUpdatedAtDesc,
                true,
            ),
            'openTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_PARTICIPANT],
                [UserStatus::Open],
                $event,
                $user,
                $orderByUpdatedAtDesc,
                true,
            ),
            'openIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [UserStatus::Open],
                $event,
                $user,
                $orderByUpdatedAtDesc,
                true,
            ),
            'openGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_GUEST],
                [UserStatus::Open],
                $event,
                $user,
                $orderByUpdatedAtDesc,
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
        $orderByUpdatedAtDesc = new Order(Order::FILED_UPDATED_AT, Order::DIRECTION_DESC);

        return $this->view->render($response, 'admin/approve-admin.twig', [
            'closedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [UserStatus::Closed],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'closedTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_LEADER],
                [UserStatus::Closed],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'closedTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_PARTICIPANT],
                [UserStatus::Closed],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'closedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [UserStatus::Closed],
                $event,
                $user,
                $orderByUpdatedAtDesc,
            ),
            'closedGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_GUEST],
                [UserStatus::Closed],
                $event,
                $user,
                $orderByUpdatedAtDesc,
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
            . $participantId . ' and role ' . $participant->role . ' with reason: ' . $reason);

        return $this->redirect($request, $response, 'admin-show-approving');
    }

    public function showPayments(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/payments-admin.twig', [
            'approvedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [UserStatus::Approved],
                $event,
                $user,
            ),
            'approvedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [UserStatus::Approved],
                $event,
                $user,
            ),
            'approvedTroopLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_LEADER],
                [UserStatus::Approved],
                $event,
                $user,
            ),
            'approvedTroopParticipants' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_TROOP_PARTICIPANT],
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
            $participant->registrationCloseDate = new DateTimeImmutable();
            $this->participantRepository->persist($participant);
            $this->paymentService->confirmPayment($payment);
            $this->flashMessages->success($this->translator->trans('flash.success.comfirmPayment'));
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
        $result = $this->bankPaymentService->setBreakpoint(new \DateTimeImmutable('2022-01-01'), $event);

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

        $userFrom = $this->userRepository->findUserFromEmailEvent($emailFrom, $event);
        $userTo = $this->userRepository->findUserFromEmailEvent($emailTo, $event);

        $participantFrom = ($userFrom === null ? null : $this->getParticipantFromUser($userFrom));
        $participantTo = ($userTo === null ? null : $this->getParticipantFromUser($userTo));

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

        $userFrom = $this->userRepository->getUserFromEmailEvent($emailFrom, $event);
        $userTo = $this->userRepository->getUserFromEmailEvent($emailTo, $event);

        $participantFrom = $this->getParticipantFromUser($userFrom);
        $participantTo = $this->getParticipantFromUser($userTo);

        if (!$this->adminService->isPaymentTransferPossible(
            $participantFrom,
            $participantTo,
            $this->flashMessages,
        ) || $participantFrom === null || $participantTo === null) {
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

    public function changeAdminNote(Request $request, Response $response): Response
    {
        return new \Slim\Psr7\Response(); // TODO implement
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

    // TODO deduplicate
    public function getParticipantFromUser(User $user): ?Participant
    {
        return match ($user->role) {
            User::ROLE_PATROL_LEADER => $this->patrolService->getPatrolLeader($user),
            User::ROLE_TROOP_LEADER => $this->troopService->getTroopLeader($user),
            User::ROLE_TROOP_PARTICIPANT => $this->troopService->getTroopParticipant($user),
            User::ROLE_IST => $this->istService->getIst($user),
            User::ROLE_GUEST => $this->guestService->getGuest($user),
            default => null,
        };
    }
}
