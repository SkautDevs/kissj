<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Application\JsonWarningDTO;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use Psr\Http\Message\ResponseInterface as Response;
use stdClass;

class ProgrammeJsonController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function assign(Response $response, Event $event, int $programmeId, int $participantId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $participant = $this->participantRepository->findParticipantById($participantId, $event);
        if ($participant === null) {
            return $this->warningJsonResponse($response, 'flash.warning.programmeParticipantNotFound');
        }

        return $this->resultJsonResponse($response, $this->programmeService->adminAssign($programme, $participant));
    }

    public function remove(Response $response, Event $event, int $programmeId, int $participantId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $participant = $this->participantRepository->findParticipantById($participantId, $event);
        if ($participant === null) {
            return $this->warningJsonResponse($response, 'flash.warning.programmeParticipantNotFound');
        }

        return $this->resultJsonResponse($response, $this->programmeService->adminRemove($programme, $participant));
    }

    private function resultJsonResponse(Response $response, ProgrammeRegistrationResult $result): Response
    {
        if ($result->success === false) {
            return $this->warningJsonResponse($response, $result->translationKey);
        }

        return $this->getResponseWithJson($response, new stdClass());
    }

    private function warningJsonResponse(Response $response, string $translationKey): Response
    {
        return $this->getResponseWithJson(
            $response,
            new JsonWarningDTO($translationKey, $this->translator->trans($translationKey)),
            409,
        );
    }
}
