<?php

namespace kissj\Participant\Admin;

use GuzzleHttp\Psr7\LazyOpenStream;
use kissj\AbstractController;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\Participant\FreeParticipant\FreeParticipantService;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstService;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController extends AbstractController {
    private $participantService;
    private $paymentService;
    private $paymentRepository;
    private $bankPaymentRepository;
    private $bankPaymentService;
    private $patrolService;
    private $istService;
    private $freeParticipantService;
    private $guestService;

    public function __construct(
        ParticipantService $participantService,
        PaymentService $paymentService,
        PaymentRepository $paymentRepository,
        BankPaymentRepository $bankPaymentRepository,
        FioBankPaymentService $bankPaymentService,
        PatrolService $patrolService,
        IstService $istService,
        FreeParticipantService $freeParticipantService,
        GuestService $guestService
    ) {
        $this->participantService = $participantService;
        $this->paymentService = $paymentService;
        $this->paymentRepository = $paymentRepository;
        $this->bankPaymentRepository = $bankPaymentRepository;
        $this->bankPaymentService = $bankPaymentService;
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
        $this->paymentService->confirmPayment($payment);
        $this->flashMessages->success($this->translator->trans('flash.success.comfirmPayment'));
        $this->logger->info('Payment ID '.$paymentId. ' manually confirmed as paid');

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $payment->participant->user->event->slug]
        );
    }

    public function showFile(string $filename) {
        $uploadFolder = __DIR__.'/../../../uploads/';
        $stream = new LazyOpenStream($uploadFolder.$filename, 'r');
        $response = new \Slim\Psr7\Response(200, null, $stream);
        $response = $response->withAddedHeader('Content-Type', mime_content_type($uploadFolder.$filename));

        return $response;
    }

    public function showAutoPayments(Response $response): Response {
        $bankPayments = $this->bankPaymentRepository->findBy([], ['id' => false]);

        return $this->view->render($response, 'admin/paymentsAuto-admin.twig', ['bankPayments' => $bankPayments]);
    }

    public function setBreakpointFromRoute(Request $request, Response $response): Response {
        $result = $this->bankPaymentService->setBreakpoint(new \DateTimeImmutable('2020-05-31'));

        if ($result) {
            $this->flashMessages->success('Set breakpoint successfully');
        } else {
            $this->flashMessages->error('Something gone wrong, probably unvalid token :(');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }

    public function updatePayments(Request $request, Response $response): Response {
        $this->paymentService->updatePayments(5);

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }
}
