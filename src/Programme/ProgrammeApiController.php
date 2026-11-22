<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Programme\DTO\ProgrammeApiErrorDTO;
use kissj\Programme\DTO\ProgrammeApiImageDTO;
use kissj\Programme\DTO\ProgrammeApiListDTO;
use kissj\Programme\DTO\ProgrammeApiOkDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantIdsDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantProgrammesDTO;
use kissj\Programme\DTO\ProgrammeApiPairingApprovedDTO;
use kissj\Programme\DTO\ProgrammeApiPairingStartDTO;
use kissj\Programme\DTO\ProgrammeApiPairingStatusDTO;
use kissj\Programme\DTO\ProgrammeApiPlaceDTO;
use kissj\Programme\DTO\ProgrammeApiProgrammeDTO;
use kissj\Programme\DTO\ProgrammeApiRegistrationWindowDTO;
use kissj\Programme\DTO\ProgrammeApiSectionDTO;
use kissj\Programme\Device\DevicePairing;
use kissj\Programme\Device\DevicePairingPollStatus;
use kissj\Programme\Device\DevicePairingService;
use kissj\Programme\Device\DeviceSession;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;

class ProgrammeApiController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeRegistrationRepository $programmeRegistrationRepository,
        private readonly DevicePairingService $devicePairingService,
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammePlaceRepository $programmePlaceRepository,
        private readonly ProgrammeImageRepository $programmeImageRepository,
    ) {
    }

    public function list(Request $request, Response $response, Event $authorizedEvent): Response
    {
        return $this->getResponseWithJson($response, new ProgrammeApiListDTO(
            array_map(
                $this->presentSection(...),
                $this->programmeSectionRepository->findForEvent($authorizedEvent),
            ),
            array_map(
                $this->presentPlace(...),
                $this->programmePlaceRepository->findForEvent($authorizedEvent),
            ),
            $this->presentProgrammes($request, $this->programmeRepository->findActiveForEvent($authorizedEvent)),
            $this->presentRegistrationWindow($authorizedEvent),
        ));
    }

    public function participantBySession(
        Request $request,
        Response $response,
        Participant $sessionParticipant,
    ): Response {
        return $this->getResponseWithJson(
            $response,
            $this->presentParticipantProgrammes($request, $sessionParticipant),
        );
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

    public function revokeSession(Request $request, Response $response): Response
    {
        $session = $request->getAttribute('deviceSession');
        if ($session instanceof DeviceSession === false) {
            throw new LogicException('Session revoke runs without device session');
        }

        $this->devicePairingService->revokeSession($session);

        return $this->getResponseWithJson($response, new ProgrammeApiOkDTO(true));
    }

    public function programmeParticipants(Response $response, Event $authorizedEvent, int $programmeId): Response
    {
        $programme = $this->programmeRepository->findActiveById($programmeId, $authorizedEvent);
        if ($programme === null) {
            return $response->withStatus(404);
        }

        return $this->getResponseWithJson($response, new ProgrammeApiParticipantIdsDTO(
            array_map(
                static fn (ProgrammeRegistration $registration): int => $registration->participant->id,
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
            'dashboard',
            ['eventSlug' => $authorizedEvent->slug],
        ) . '#devicePairing';

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

    private function presentParticipantProgrammes(
        Request $request,
        Participant $participant,
    ): ProgrammeApiParticipantProgrammesDTO {
        return new ProgrammeApiParticipantProgrammesDTO(
            new ProgrammeApiParticipantDTO($participant->id, $participant->nickname),
            $this->presentProgrammes($request, $this->programmeRepository->findActiveForParticipant($participant)),
        );
    }

    /**
     * @param list<Programme> $programmes
     * @return list<ProgrammeApiProgrammeDTO>
     */
    private function presentProgrammes(Request $request, array $programmes): array
    {
        $imagesByProgramme = $this->programmeImageRepository->findForProgrammes($programmes);
        $router = $this->getRouter($request);
        $imageBaseUri = $this->getImageBaseUri($request);
        $presentImage = static fn (ProgrammeImage $image): ProgrammeApiImageDTO => new ProgrammeApiImageDTO(
            $router->fullUrlFor($imageBaseUri, 'programme-api-image', ['filename' => $image->filename]),
            $router->fullUrlFor($imageBaseUri, 'programme-api-image-thumb', ['filename' => $image->filename]),
            $image->alt,
        );

        return array_map(
            fn (Programme $programme): ProgrammeApiProgrammeDTO => $this->programmeService->presentProgramme(
                $programme,
                array_map($presentImage, $imagesByProgramme[$programme->id] ?? []),
            ),
            $programmes,
        );
    }

    /**
     * TLS ends at the ingress, so PHP sees plain http, and eventApp drops image URLs that are not https.
     * Caddy overwrites X-Forwarded-Proto with its own scheme, so the ingress's X-Forwarded-Scheme and X-Scheme,
     * which Caddy passes through, are read too.
     */
    private function getImageBaseUri(Request $request): UriInterface
    {
        $uri = $request->getUri();
        if ($this->isForwardedAsHttps($request) === false) {
            return $uri;
        }

        $httpsUri = $uri->withScheme('https');

        return $uri->getPort() === null ? $httpsUri->withPort(null) : $httpsUri;
    }

    private function isForwardedAsHttps(Request $request): bool
    {
        foreach (['X-Forwarded-Proto', 'X-Forwarded-Scheme', 'X-Scheme'] as $headerName) {
            $firstValue = explode(',', $request->getHeaderLine($headerName))[0];
            if (strtolower(trim($firstValue)) === 'https') {
                return true;
            }
        }

        return false;
    }

    private function presentPlace(ProgrammePlace $place): ProgrammeApiPlaceDTO
    {
        return new ProgrammeApiPlaceDTO(
            $place->id,
            $place->name,
            $place->lat === null ? null : (float)$place->lat,
            $place->lng === null ? null : (float)$place->lng,
        );
    }

    private function presentRegistrationResult(Response $response, ProgrammeRegistrationResult $result): Response
    {
        if ($result->error === null) {
            return $this->getResponseWithJson($response, new ProgrammeApiOkDTO(true));
        }

        return $this->getResponseWithJson($response, new ProgrammeApiErrorDTO($result->error->value), 400);
    }

    private function presentRegistrationWindow(Event $event): ProgrammeApiRegistrationWindowDTO
    {
        $start = $event->programmeRegistrationStart;
        $end = $event->programmeRegistrationEnd;

        return new ProgrammeApiRegistrationWindowDTO(
            $start === null ? null : DateTimeUtils::formatAtom($start),
            $end === null ? null : DateTimeUtils::formatAtom($end),
        );
    }

    private function presentSection(ProgrammeSection $section): ProgrammeApiSectionDTO
    {
        return new ProgrammeApiSectionDTO($section->id, $section->name, $section->subtitle);
    }
}
