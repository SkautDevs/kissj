<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\DTO\ProgrammeApiEnrolmentWindowDTO;
use kissj\Programme\DTO\ProgrammeApiErrorDTO;
use kissj\Programme\DTO\ProgrammeApiImageDTO;
use kissj\Programme\DTO\ProgrammeApiListDTO;
use kissj\Programme\DTO\ProgrammeApiOkDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantIdsDTO;
use kissj\Programme\DTO\ProgrammeApiParticipantProgrammesDTO;
use kissj\Programme\DTO\ProgrammeApiPlaceDTO;
use kissj\Programme\DTO\ProgrammeApiProgrammeDTO;
use kissj\Programme\DTO\ProgrammeApiSectionDTO;
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;

class ProgrammeApiController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
        private readonly ProgrammeEnrolmentService $programmeEnrolmentService,
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
            $this->presentEnrolmentWindow($authorizedEvent),
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

    public function enrol(Response $response, Participant $sessionParticipant, int $programmeId): Response
    {
        return $this->presentEnrolmentResult(
            $response,
            $this->programmeEnrolmentService->enrol($sessionParticipant, $programmeId),
        );
    }

    public function withdraw(Response $response, Participant $sessionParticipant, int $programmeId): Response
    {
        return $this->presentEnrolmentResult(
            $response,
            $this->programmeEnrolmentService->withdraw($sessionParticipant, $programmeId),
        );
    }

    public function programmeParticipants(Response $response, Event $authorizedEvent, int $programmeId): Response
    {
        $programme = $this->programmeRepository->findActiveById($programmeId, $authorizedEvent);
        if ($programme === null) {
            return $response->withStatus(404);
        }

        return $this->getResponseWithJson($response, new ProgrammeApiParticipantIdsDTO(
            array_map(
                static fn (ProgrammeEnrolment $enrolment): int => $enrolment->participant->id,
                $this->programmeEnrolmentRepository->findActiveNotCancelledForProgramme($programme),
            ),
        ));
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
            fn (Programme $programme): ProgrammeApiProgrammeDTO => $this->presentProgramme(
                $programme,
                array_map($presentImage, $imagesByProgramme[$programme->id] ?? []),
            ),
            $programmes,
        );
    }

    /**
     * @param list<ProgrammeApiImageDTO> $images
     */
    private function presentProgramme(Programme $programme, array $images): ProgrammeApiProgrammeDTO
    {
        return new ProgrammeApiProgrammeDTO(
            $programme->id,
            $programme->name,
            $programme->section->id,
            $programme->description,
            $programme->getPlaceName(),
            $programme->programmePlace?->id,
            DateTimeUtils::formatAtom($programme->start),
            DateTimeUtils::formatAtom($programme->end),
            $programme->isAutoenrolled,
            array_map(
                static fn (ParticipantRole $role): string => $role->value,
                $programme->roles,
            ),
            $images,
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

    private function presentEnrolmentResult(Response $response, ProgrammeEnrolmentResult $result): Response
    {
        if ($result->error === null) {
            return $this->getResponseWithJson($response, new ProgrammeApiOkDTO(true));
        }

        return $this->getResponseWithJson($response, new ProgrammeApiErrorDTO($result->error->value), 400);
    }

    private function presentEnrolmentWindow(Event $event): ProgrammeApiEnrolmentWindowDTO
    {
        $start = $event->programmeEnrolmentStart;
        $end = $event->programmeEnrolmentEnd;

        return new ProgrammeApiEnrolmentWindowDTO(
            $start === null ? null : DateTimeUtils::formatAtom($start),
            $end === null ? null : DateTimeUtils::formatAtom($end),
        );
    }

    private function presentSection(ProgrammeSection $section): ProgrammeApiSectionDTO
    {
        return new ProgrammeApiSectionDTO($section->id, $section->name, $section->subtitle);
    }
}
