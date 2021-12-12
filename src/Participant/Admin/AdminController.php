<?php

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\Event\Event;
use kissj\Participant\Guest\GuestService;
use kissj\Participant\Ist\IstService;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
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
        private AdminService $adminService,
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
                'guests' => $this->guestService->getAllGuestsStatistics($event, $user),
            ],
        );
    }

    public function showApproving(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/approve-admin.twig', [
            'closedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [USER::STATUS_CLOSED],
                $event,
                $user,
            ),
            'closedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [USER::STATUS_CLOSED],
                $event,
                $user,
            ),
            'closedGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_GUEST],
                [USER::STATUS_CLOSED],
                $event,
                $user,
            ),
            'caIst' => $event->getEventType()->getContentArbiterIst(),
            'caPl' => $event->getEventType()->getContentArbiterPatrolLeader(),
            'caPp' => $event->getEventType()->getContentArbiterPatrolParticipant(),
            'caGuest' => $event->getEventType()->getContentArbiterGuest(),
        ]);
    }

    public function showDenyParticipant(int $participantId, Response $response): Response
    {
        $participant = $this->participantRepository->get($participantId);

        return $this->view->render($response, 'admin/deny-admin.twig', ['participant' => $participant]);
    }

    public function denyParticipant(int $participantId, Request $request, Response $response): Response
    {
        // TODO check if correct event
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var Participant $participant */
        $participant = $this->participantRepository->get($participantId);
        $this->participantService->denyRegistration($participant, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.istDenied')); // TODO re-word deny flash message
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
            'approvedPatrolLeaders' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_PATROL_LEADER],
                [USER::STATUS_APPROVED],
                $event,
                $user,
            ),
            'approvedIsts' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_IST],
                [USER::STATUS_APPROVED],
                $event,
                $user,
            ),
            'approvedGuests' => $this->participantRepository->getAllParticipantsWithStatus(
                [User::ROLE_GUEST],
                [USER::STATUS_APPROVED],
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

    public function cancelPayment(int $paymentId, Request $request, Response $response): Response
    {
        // TODO check if correct event
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var Payment $payment */
        $payment = $this->paymentRepository->get($paymentId);
        $this->participantService->cancelPayment($payment, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.paymentCanceled'));
        $this->logger->info('Cancelled payment ID ' . $paymentId . ' for participant with reason: ' . $reason);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $payment->participant->user->event->slug]
        );
    }

    public function cancelAllDuePayments(Request $request, Response $response): Response
    {
        // TODO check if correct event!!
        $this->paymentService->cancelDuePayments(5);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }

    public function confirmPayment(int $paymentId, Request $request, Response $response): Response
    {
        // TODO check if correct event
        /** @var Payment $payment */
        $payment = $this->paymentRepository->get($paymentId);
        $this->paymentService->confirmPayment($payment);
        $this->flashMessages->success($this->translator->trans('flash.success.comfirmPayment'));
        $this->logger->info('Payment ID ' . $paymentId . ' manually confirmed as paid');

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
            ['eventSlug' => $payment->participant->user->event->slug]
        );
    }

    public function showFile(string $filename)
    {
        // TODO check if correct event
        $file = $this->fileHandler->getFile($filename);
        $response = new \Slim\Psr7\Response(200, null, $file->stream);
        $response = $response->withAddedHeader('Content-Type', $file->mimeContentType);

        return $response;
    }

    public function showAutoPayments(Response $response): Response
    {
        $arguments = [
            'bankPayments' => $this->bankPaymentRepository->findBy([], ['id' => false]),
            'bankPaymentsTodo' => $this->bankPaymentRepository->findBy(
                ['status' => BankPayment::STATUS_UNKNOWN],
                ['id' => false]
            ),
        ];

        return $this->view->render($response, 'admin/paymentsAuto-admin.twig', $arguments);
    }

    public function setBreakpointFromRoute(Request $request, Response $response): Response
    {
        // TODO check if correct event
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

    public function updatePayments(Request $request, Response $response): Response
    {
        // TODO check if correct event
        $this->paymentService->updatePayments(5);

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }

    public function markBankPaymentPaired(Request $request, Response $response, int $paymentId): Response
    {
        // TODO check if correct event
        $notice = htmlspecialchars($request->getParsedBody()['notice'], ENT_QUOTES);
        $this->bankPaymentService->setBankPaymentPaired($paymentId);
        $this->logger->info('Payment with ID ' . $paymentId . ' has been marked as paired with notice: ' . $notice);
        $this->flashMessages->info($this->translator->trans('flash.info.markedAsPaired'));

        return $this->redirect(
            $request,
            $response,
            'admin-show-auto-payments',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
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
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }

    public function showTransferPayment(Request $request, Response $response): Response
    {
        // TODO check if correct event
        $queryParams = $request->getQueryParams();

        $emailFrom = $queryParams['emailFrom'];
        $emailTo = $queryParams['emailTo'];

        $participantFrom = $this->participantService->findParticipantFromUserMail($emailFrom);
        $participantTo = $this->participantService->findParticipantFromUserMail($emailTo);

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

    public function transferPayment(Request $request, Response $response): Response
    {
        // TODO check if correct event
        $queryParams = $request->getParsedBody();

        $participantFrom = $this->participantService->findParticipantFromUserMail($queryParams['emailFrom']);
        $participantTo = $this->participantService->findParticipantFromUserMail($queryParams['emailTo']);

        // TODO refactor findParticipantFromUserMail into get method
        if ($participantFrom === null || $participantTo === null) {
            throw new \RuntimeException('Found no participant');
        }

        if (!$this->adminService->isPaymentTransferPossible(
            $participantFrom,
            $participantTo,
            $this->flashMessages
        )) {
            throw new \RuntimeException('Cannot do transfer');
        }

        $this->adminService->transferPayment($participantFrom, $participantTo);
        $this->flashMessages->success($this->translator->trans('flash.success.transfer'));

        return $this->redirect(
            $request,
            $response,
            'admin-dashboard',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }
}
