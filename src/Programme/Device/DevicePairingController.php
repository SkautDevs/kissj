<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

class DevicePairingController extends AbstractController
{
    public function __construct(
        private readonly DevicePairingService $devicePairingService,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function showForm(Response $response, string $csrfName, string $csrfValue): Response
    {
        return $this->view->render($response, 'participant/devicePairing.twig', [
            'csrfName' => $csrfName,
            'csrfValue' => $csrfValue,
        ]);
    }

    public function check(
        Request $request,
        Response $response,
        Event $event,
        User $user,
        string $csrfName,
        string $csrfValue,
    ): Response {
        $pairing = $this->devicePairingService->findPendingForParticipant(
            $this->getParameterFromBody($request, 'code'),
            $event,
            $this->participantRepository->getParticipantFromUser($user),
        );
        if ($pairing instanceof DevicePairingRefusal) {
            $this->flashMessages->warning($pairing->flashKey());

            return $this->redirect($request, $response, 'showDevicePairing');
        }

        return $this->view->render($response, 'participant/devicePairing.twig', [
            'pairing' => $pairing,
            'csrfName' => $csrfName,
            'csrfValue' => $csrfValue,
        ]);
    }

    public function confirm(Request $request, Response $response, Event $event, User $user): Response
    {
        $decisionValue = $this->getParameterFromBody($request, 'decision');
        $decision = DevicePairingDecision::tryFrom($decisionValue)
            ?? throw new RuntimeException('Unknown device pairing decision ' . $decisionValue);

        $refusal = $this->devicePairingService->decide(
            $this->getParameterFromBody($request, 'code'),
            $decision,
            $event,
            $this->participantRepository->getParticipantFromUser($user),
        );
        if ($refusal !== null) {
            $this->flashMessages->warning($refusal->flashKey());

            return $this->redirect($request, $response, 'showDevicePairing');
        }

        if ($decision === DevicePairingDecision::Approve) {
            $this->flashMessages->success('flash.success.devicePaired');
        } else {
            $this->flashMessages->info('flash.info.devicePairingDenied');
        }

        return $this->redirect($request, $response, 'dashboard');
    }
}
