<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Participant\Patrol\PatrolParticipant;
use kissj\AbstractController;
use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentSource;
use kissj\Payment\PaymentStatus;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminPaymentController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PaymentService $paymentService,
        private readonly PaymentRepository $paymentRepository,
        private readonly BankPaymentRepository $bankPaymentRepository,
        private readonly AdminService $adminService,
        private readonly PaymentTransferService $paymentTransferService,
        private readonly UserRepository $userRepository,
        private readonly Metrics $metrics,
    ) {
    }

    public function showPayments(
        Response $response,
        Event $event,
        User $user,
    ): Response {
        return $this->view->render($response, 'admin/payments-admin.twig', [
            'sections' => $this->adminService->getParticipantSections(
                $event,
                $user,
                UserStatus::Approved,
                [
                    'ist' => 'payments-admin.allIstsPaid',
                    'pl' => 'payments-admin.allPatrolsPaid',
                    'tl' => 'payments-admin.allTroopLeadersPaid',
                    'ot' => 'payments-admin.allOrganizingTeamPaid',
                ],
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

        $this->participantService->cancelPayment($payment, $reason, PaymentSource::ManualAdmin);
        $this->flashMessages->info('flash.info.paymentCanceled');
        $this->logger->info('Cancelled payment ID ' . $paymentId . ' for participant with reason: ' . $reason);

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
    }

    public function showChangePaymentPrice(
        Request $request,
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $payment = $this->paymentRepository->getById($paymentId, $event);

        if ($payment->status !== PaymentStatus::Waiting) {
            $this->flashMessages->warning('flash.warning.paymentNotWaitingCannotChangePrice');

            return $this->redirect($request, $response, 'admin-show-payments');
        }

        return $this->view->render($response, 'admin/changePaymentPrice.twig', ['payment' => $payment]);
    }

    public function changePaymentPrice(
        Request $request,
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $newPrice = (int)$this->getParameterFromBody($request, 'newPrice', true);
        $reason = $this->getParameterFromBody($request, 'reason', true);
        $payment = $this->paymentRepository->getById($paymentId, $event);

        if ($payment->status !== PaymentStatus::Waiting) {
            $this->flashMessages->warning('flash.warning.paymentNotWaitingCannotChangePrice');

            return $this->redirect($request, $response, 'admin-show-payments');
        }

        if ($newPrice < 0 || $newPrice > 99999) {
            $this->flashMessages->warning('flash.warning.paymentNotWaitingCannotChangePrice');

            return $this->redirect($request, $response, 'admin-show-payments');
        }

        $this->participantService->changePaymentPrice($payment, $newPrice, $reason);
        $this->flashMessages->success('flash.success.paymentPriceChanged');
        $this->logger->info('Payment ID ' . $paymentId . ' price changed to ' . $newPrice . ' with reason: ' . $reason);
        $this->metrics->count(MetricName::PaymentsPriceChanged, 1);

        return $this->redirect($request, $response, 'admin-show-payments');
    }

    public function cancelAllDuePayments(
        Request $request,
        Response $response,
        User $user,
    ): Response {
        $this->flashPaymentResult($this->paymentService->cancelDuePayments($user->event));

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

        $this->flashPaymentResult($this->paymentService->confirmPayment($payment, PaymentSource::ManualAdmin));
        $this->logger->info('Payment ID ' . $paymentId . ' manually confirmed as paid');

        return $this->redirect(
            $request,
            $response,
            'admin-show-payments',
        );
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
        $this->flashPaymentResult($this->paymentService->updatePayments($event));

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
        $this->metrics->count(MetricName::PaymentsMarkedPaired, 1);
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
        $this->metrics->count(MetricName::PaymentsMarkedUnrelated, 1);
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
            'transferPossible' => $this->paymentTransferService->isPaymentTransferPossible(
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

        if (!$this->paymentTransferService->isPaymentTransferPossible(
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
            try {
                $this->paymentTransferService->transferPayment($participantFrom, $participantTo);
                $this->flashMessages->success('flash.success.transfer');
            } catch (\RuntimeException $e) {
                $this->flashMessages->error('flash.error.transferFailed');
                $this->sentryCollector->collect($e);
            }
        }

        return $this->redirect(
            $request,
            $response,
            'admin-dashboard',
        );
    }

    public function showAddNewPayment(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        return $this->view->render($response, 'admin/addNewPayment.twig', [
            'participant' => $participant,
        ]);
    }

    public function addNewPayment(
        Request $request,
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $price = (int)$this->getParameterFromBody($request, 'price', true);
        $reason = $this->getParameterFromBody($request, 'reason', true);

        $participant = $this->participantRepository->findParticipantById($participantId, $event);
        if ($participant === null) {
            $this->flashMessages->warning('flash.warning.participantNotFoundAddPaymentNotPossible');
        } elseif ($participant instanceof PatrolParticipant) {
            $this->flashMessages->warning('flash.warning.patrolParticipantCannotHavePayment');
        } elseif ($participant->getUserButNotNull()->status->isUnfitForNewPayment()) {
            $this->flashMessages->warning('flash.warning.participantNotInCorrectStatusForAddPayment');
        } else {
            $this->participantService->addNewPayment($participant, $price, $reason);
            $this->flashMessages->success('flash.success.paymentAdded');
        }

        return $this->redirect(
            $request,
            $response,
            'admin-mend-participant',
            ['participantId' => (string)$participantId],
        );
    }

    // TODO shift into "approved" event to generate multiple payments, with different dates even
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

            $this->flashMessages->info('flash.info.generatedPayments', ['%count%' => (string)$count]);
        }

        return $this->redirect(
            $request,
            $response,
            'admin-show-stats',
        );
    }
}
