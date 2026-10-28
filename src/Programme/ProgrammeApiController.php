<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Programme\DTO\ProgrammeApiErrorDTO;
use kissj\Programme\DTO\ProgrammeApiListDTO;
use kissj\Programme\DTO\ProgrammeApiOkDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantProgrammesDTO;
use kissj\Programme\DTO\ProgrammeApiPairingApprovedDTO;
use kissj\Programme\DTO\ProgrammeApiPairingStartDTO;
use kissj\Programme\DTO\ProgrammeApiPairingStatusDTO;
use kissj\Programme\DTO\ProgrammeApiSectionDTO;
use kissj\Programme\DTO\ProgrammeApiTieCodesDTO;
use kissj\Programme\Device\DevicePairing;
use kissj\Programme\Device\DevicePairingPollStatus;
use kissj\Programme\Device\DevicePairingService;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeApiController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeRegistrationRepository $programmeRegistrationRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly DevicePairingService $devicePairingService,
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

        return $this->getResponseWithJson($response, $this->presentParticipantProgrammes($participant));
    }

    public function participantBySession(Response $response, Participant $sessionParticipant): Response
    {
        return $this->getResponseWithJson($response, $this->presentParticipantProgrammes($sessionParticipant));
    }

    public function register(Response $response, Participant $sessionParticipant, int $programmeId): Response
    {
        return $this->presentRegistrationResult(
            $response,
            $this->programmeService->register($sessionParticipant, $programmeId),
        );
    }

    public function unregister(Response $response, Participant $sessionParticipant, int $programmeId): Response
    {
        return $this->presentRegistrationResult(
            $response,
            $this->programmeService->unregister($sessionParticipant, $programmeId),
        );
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

    public function pairingStart(Request $request, Response $response, Event $authorizedEvent): Response
    {
        $deviceId = $this->getParsedJsonFromBody($request)['deviceId'] ?? null;
        if (is_string($deviceId) === false || preg_match('/^[0-9a-f]{64}$/D', $deviceId) !== 1) {
            $response->getBody()->write('Invalid deviceId');

            return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
        }

        $pairing = $this->devicePairingService->start($authorizedEvent, $deviceId);
        if ($pairing === null) {
            $response->getBody()->write('Too many pending pairings');

            return $response->withStatus(429)->withHeader('Content-Type', 'text/plain');
        }

        $verificationUrl = $this->getRouter($request)->fullUrlFor(
            $request->getUri(),
            'showDevicePairing',
            ['eventSlug' => $authorizedEvent->slug],
        );

        return $this->getResponseWithJson($response, new ProgrammeApiPairingStartDTO(
            $pairing->deviceCode,
            $pairing->userCode,
            $verificationUrl,
            DateTimeUtils::formatAtom($pairing->expiresAt),
            DevicePairing::POLL_INTERVAL_SECONDS,
        ));
    }

    public function pairingPoll(Request $request, Response $response, Event $authorizedEvent): Response
    {
        $deviceCode = $this->getParsedJsonFromBody($request)['deviceCode'] ?? null;
        $result = $this->devicePairingService->poll(is_string($deviceCode) ? $deviceCode : '', $authorizedEvent);

        if ($result->status !== DevicePairingPollStatus::Approved) {
            return $this->getResponseWithJson($response, new ProgrammeApiPairingStatusDTO($result->status->value));
        }

        $programmeApiPairingApprovedDTO = new ProgrammeApiPairingApprovedDTO(
            $result->status->value,
            $result->sessionToken ?? throw new LogicException('Approved poll result has no session token'),
            DateTimeUtils::formatAtom(
                $result->sessionExpiresAt ?? throw new LogicException('Approved poll result has no expiry'),
            ),
        );

        return $this->getResponseWithJson($response, $programmeApiPairingApprovedDTO);

    }

    private function presentParticipantProgrammes(Participant $participant): ProgrammeApiParticipantProgrammesDTO
    {
        return new ProgrammeApiParticipantProgrammesDTO(
            new ProgrammeApiParticipantDTO($participant->nickname),
            array_map(
                $this->programmeService->presentProgramme(...),
                $this->programmeRepository->findActiveForParticipant($participant),
            ),
        );
    }

    private function presentRegistrationResult(Response $response, ProgrammeRegistrationResult $result): Response
    {
        if ($result->success) {
            return $this->getResponseWithJson($response, new ProgrammeApiOkDTO(true));
        }

        return $this->getResponseWithJson(
            $response,
            new ProgrammeApiErrorDTO(
                ($result->error ?? throw new LogicException('Failed programme registration has no error'))->value,
            ),
            400,
        );
    }

    private function presentSection(ProgrammeSection $section): ProgrammeApiSectionDTO
    {
        return new ProgrammeApiSectionDTO($section->id, $section->name, $section->subtitle);
    }
}
