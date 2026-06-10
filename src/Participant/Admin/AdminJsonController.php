<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantException;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Payment\PaymentMessageSeverity;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminJsonController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PaymentService $paymentService,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function approveParticipant(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        try {
            $this->participantService->approveRegistration($participant);
        } catch (ParticipantException $e) {
            return $this->warningJsonResponse($response, $e->translationKey);
        }

        return $this->successfulJsonResponse($response);
    }

    public function confirmPayment(
        Response $response,
        Event $event,
        int $paymentId,
    ): Response {
        $payment = $this->paymentRepository->getById($paymentId, $event);

        try {
            $result = $this->paymentService->confirmPayment($payment);
        } catch (\RuntimeException) {
            return $this->warningJsonResponse($response, 'flash.warning.paymentNotWaiting');
        }

        foreach ($result->messages as $message) {
            if ($message->severity === PaymentMessageSeverity::Warning) {
                return $this->warningJsonResponse($response, $message->translationKey);
            }
        }

        $this->logger->info('Payment ID ' . $paymentId . ' manually confirmed as paid');

        return $this->successfulJsonResponse($response);
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

    private function successfulJsonResponse(Response $response): Response
    {
        return $this->getResponseWithJson($response, new \stdClass());
    }

    private function warningJsonResponse(Response $response, string $translationKey): Response
    {
        return $this->getResponseWithJson(
            $response,
            [
                'translationKey' => $translationKey,
                'translationMessage' => $this->translator->trans($translationKey),
            ],
            409,
        );
    }
}
