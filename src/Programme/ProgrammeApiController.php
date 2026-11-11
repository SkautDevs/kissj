<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Programme\DTO\ProgrammeApiListDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantProgrammesDTO;
use kissj\Programme\DTO\ProgrammeApiSectionDTO;
use kissj\Programme\DTO\ProgrammeApiTieCodesDTO;
use Psr\Http\Message\ResponseInterface as Response;

class ProgrammeApiController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeRegistrationRepository $programmeRegistrationRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly ProgrammeService $programmeService,
    ) {
    }

    public function list(Response $response, Event $authorizedEvent): Response
    {
        return $this->getResponseWithJson($response, new ProgrammeApiListDTO(
            array_map(
                $this->presentSection(...),
                $this->programmeSectionRepository->findForEvent($authorizedEvent),
            ),
            array_map(
                $this->programmeService->presentProgramme(...),
                $this->programmeRepository->findActiveForEvent($authorizedEvent),
            ),
        ));
    }

    public function participantByTieCode(Response $response, Event $authorizedEvent, string $tieCode): Response
    {
        $participant = $this->participantRepository->findOneNotCancelledByTieCodeAndEvent($tieCode, $authorizedEvent);
        if ($participant === null) {
            return $response->withStatus(404);
        }

        return $this->getResponseWithJson($response, new ProgrammeApiParticipantProgrammesDTO(
            new ProgrammeApiParticipantDTO($participant->nickname),
            array_map(
                $this->programmeService->presentProgramme(...),
                $this->programmeRepository->findActiveForParticipant($participant),
            ),
        ));
    }

    public function programmeParticipants(Response $response, Event $authorizedEvent, int $programmeId): Response
    {
        $programme = $this->programmeRepository->findActiveById($programmeId, $authorizedEvent);
        if ($programme === null) {
            return $response->withStatus(404);
        }

        return $this->getResponseWithJson($response, new ProgrammeApiTieCodesDTO(
            array_map(
                static fn (ProgrammeRegistration $registration): string => $registration->participant->tieCode,
                $this->programmeRegistrationRepository->findActiveNotCancelledForProgramme($programme),
            ),
        ));
    }

    private function presentSection(ProgrammeSection $section): ProgrammeApiSectionDTO
    {
        return new ProgrammeApiSectionDTO($section->id, $section->name, $section->subtitle);
    }
}
