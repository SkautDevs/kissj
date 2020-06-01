<?php

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\Participant\FreeParticipant\FreeParticipantService;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController extends AbstractController {
    public $participantService;
    public $participantRepository;
    public $paymentService;
    public $paymentRepository;
    public $patrolService;
    public $istService;
    public $freeParticipantService;
    public $guestService;

    public function __construct(
        ParticipantService $participantService,
        ParticipantRepository $participantRepository,
        PaymentService $paymentService,
        PaymentRepository $paymentRepository,
        PatrolService $patrolService,
        IstService $istService,
        FreeParticipantService $freeParticipantService,
        GuestService $guestService
    ) {
        $this->participantService = $participantService;
        $this->participantRepository = $participantRepository;
        $this->paymentService = $paymentService;
        $this->paymentRepository = $paymentRepository;
        $this->patrolService = $patrolService;
        $this->istService = $istService;
        $this->freeParticipantService = $freeParticipantService;
        $this->guestService = $guestService;
    }

    public function showDashboard(Response $response): Response {
        return $this->view->render(
            $response,
            'admin/dashboard-admin.twig',
            [
                'patrols' => $this->patrolService->getAllPatrolsStatistics(),
                'ists' => $this->istService->getAllIstsStatistics(),
                'freeParticipants' => $this->freeParticipantService->getAllFreeParticipantStatistics(),
                'guests' => $this->guestService->getAllGuestsStatistics(),
            ]
        );
    }

    public function showApproving(
        Response $response,
        ParticipantService $participantService
    ): Response {
        return $this->view->render($response, 'admin/approving-admin.twig', [
            'closedPatrolLeaders' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_PATROL_LEADER, USER::STATUS_CLOSED),
            'closedIsts' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_IST, USER::STATUS_CLOSED),
            'closedFreeParticipants' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_FREE_PARTICIPANT, USER::STATUS_CLOSED),
            'closedGuests' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_GUEST, USER::STATUS_CLOSED),
        ]);
    }

    public function showPayments(
        Response $response,
        ParticipantService $participantService
    ): Response {
        return $this->view->render($response, 'admin/payments-admin.twig', [
            'approvedPatrolLeaders' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_PATROL_LEADER, USER::STATUS_APPROVED),
            'approvedIsts' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_IST, USER::STATUS_APPROVED),
            'approvedFreeParticipants' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_FREE_PARTICIPANT, USER::STATUS_APPROVED),
            'approvedGuests' => $participantService
                ->getAllParticipantsWithStatus(User::ROLE_GUEST, USER::STATUS_APPROVED),
        ]);
    }

    public function showCancelPayment(int $paymentId, Response $response): Response {
        $payment = $this->paymentRepository->find($paymentId);

        return $this->view->render($response, 'admin/cancelPayment-admin.twig', ['payment' => $payment]);
    }

    public function cancelPayment(int $paymentId, Request $request, Response $response): Response {
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);

        $payment = $this->paymentRepository->find($paymentId);
        $this->participantService->cancelPayment($payment, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.paymentCanceled'));
        $this->logger->info('Cancelled payment ID '.$paymentId.' for participant with reason: '.$reason);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $payment->participant->user->event->slug]
        );
    }

    public function confirmPayment(int $paymentId, Request $request, Response $response): Response {
        $payment = $this->paymentRepository->find($paymentId);
        $this->participantService->confirmPayment($payment);
        $this->flashMessages->success($this->translator->trans('flash.success.comfirmPayment'));
        $this->logger->info('Confirmed payment ID'.$paymentId);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $payment->participant->user->event->slug]
        );
    }
}
