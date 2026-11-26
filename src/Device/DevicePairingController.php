<?php

declare(strict_types=1);

namespace kissj\Device;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DevicePairingController extends AbstractController
{
    public function __construct(
        private readonly DevicePairingService $devicePairingService,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function pair(Request $request, Response $response, Event $event, User $user): Response
    {
        $refusal = $this->devicePairingService->approveEnteredCode(
            $this->getParameterFromBody($request, 'code'),
            $event,
            $this->participantRepository->getParticipantFromUser($user),
        );
        if ($refusal !== null) {
            $this->flashMessages->warning($refusal->flashKey());

            return $this->redirect($request, $response, 'dashboard', [], ['pairingRetry' => '1']);
        }

        $this->flashMessages->success('flash.success.devicePaired');

        return $this->redirect($request, $response, 'dashboard');
    }
}
