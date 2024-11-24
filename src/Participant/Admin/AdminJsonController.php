<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantException;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminJsonController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function approveParticipant(
        Response $response,
        Event $event,
        int $participantId,
    ): Response {
        $participant = $this->participantRepository->getParticipantById($participantId, $event);

        try {
            $participant = $this->participantService->approveRegistration($participant);
        } catch (ParticipantException $e) {
            return $this->getJsonResponseFromException($response, $e);
        }

        return $this->getResponseWithJson(
            $response,
            [
                'userStatus' => $participant->getUserButNotNull()->status->value,
            ]
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
}
