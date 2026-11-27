<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeImmutable;
use DateTimeZone;
use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Device\DevicePairingRepository;
use kissj\Device\DevicePairingService;
use kissj\Event\Event;
use kissj\FileHandler\UploadFileHandler;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Image\ProgrammeImageService;
use kissj\Programme\Image\ProgrammeImageUploadRefusal;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceRepository;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

class ProgrammeAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammeEnrolmentService $programmeEnrolmentService,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly UploadFileHandler $uploadFileHandler,
        private readonly ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly DevicePairingRepository $devicePairingRepository,
        private readonly DevicePairingService $devicePairingService,
        private readonly ProgrammePlaceRepository $programmePlaceRepository,
        private readonly ProgrammePlaceService $programmePlaceService,
        private readonly ProgrammeImageRepository $programmeImageRepository,
        private readonly ProgrammeImageService $programmeImageService,
    ) {
    }

    public function overview(Response $response, Event $event): Response
    {
        $programmes = $this->programmeRepository->findActiveForEvent($event);

        return $this->view->render($response, 'admin/programmes-admin.twig', [
            'programmes' => $programmes,
            'imagesByProgramme' => $this->programmeImageRepository->findForProgrammes($programmes),
            'occupancy' => $this->programmeRepository->getActiveOccupancy($event),
            'event' => $event,
            'pendingPairingCount' => $event->hasEventAppApi()
                ? $this->devicePairingRepository->countPendingUnexpired($event, DateTimeUtils::getDateTime())
                : null,
            'maxPendingPairings' => DevicePairingService::MAX_PENDING_PER_EVENT,
        ]);
    }

    public function purgePendingPairings(Request $request, Response $response, Event $event): Response
    {
        if ($event->hasEventAppApi() === false) {
            throw new HttpNotFoundException($request);
        }

        $count = $this->devicePairingService->purgePending($event);
        $this->flashMessages->success('flash.success.devicePairingsPurged', ['%count%' => (string)$count]);

        return $this->redirect($request, $response, 'admin-programmes');
    }

    public function saveWindow(Request $request, Response $response, Event $event): Response
    {
        $start = $this->getParameterFromBody($request, 'programmeEnrolmentStart');
        $end = $this->getParameterFromBody($request, 'programmeEnrolmentEnd');

        $startDate = $start === '' ? null : $this->parseDatetime($start);
        $endDate = $end === '' ? null : $this->parseDatetime($end);
        $isInvalid = ($start !== '' && $startDate === null)
            || ($end !== '' && $endDate === null)
            || ($startDate !== null && $endDate !== null && $endDate <= $startDate);
        if ($isInvalid) {
            $this->flashMessages->error('flash.error.programmeInvalidTime');

            return $this->redirect($request, $response, 'admin-programmes');
        }

        $this->programmeService->saveEnrolmentWindow($event, $startDate, $endDate);
        $this->flashMessages->success('flash.success.programmeWindowSaved');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    public function showCreate(Response $response, Event $event): Response
    {
        return $this->renderForm($response, $event, null);
    }

    public function create(Request $request, Response $response, Event $event): Response
    {
        if ($this->isBodyDroppedAsTooLarge($request)) {
            return $this->redirect($request, $response, 'admin-programme-new');
        }

        $start = $this->parseDatetime($this->getParameterFromBody($request, 'start'));
        $end = $this->parseDatetime($this->getParameterFromBody($request, 'end'));
        if ($start === null || $end === null || $end <= $start) {
            $this->flashMessages->error('flash.error.programmeInvalidTime');

            return $this->redirect($request, $response, 'admin-programme-new');
        }

        $programme = $this->programmeService->createProgramme(
            $event,
            $this->programmeDataFromRequest($request, $event, $start, $end),
        );
        $this->addUploadedImages($programme, $request);
        $this->flashMessages->success('flash.success.programmeSaved');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    public function showEdit(Response $response, Event $event, int $programmeId): Response
    {
        return $this->renderForm($response, $event, $this->programmeRepository->getActiveById($programmeId, $event));
    }

    public function update(Request $request, Response $response, Event $event, int $programmeId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        if ($this->isBodyDroppedAsTooLarge($request)) {
            return $this->redirect(
                $request,
                $response,
                'admin-programme-edit',
                ['programmeId' => (string)$programmeId],
            );
        }

        $start = $this->parseDatetime($this->getParameterFromBody($request, 'start'));
        $end = $this->parseDatetime($this->getParameterFromBody($request, 'end'));
        if ($start === null || $end === null || $end <= $start) {
            $this->flashMessages->error('flash.error.programmeInvalidTime');

            return $this->redirect(
                $request,
                $response,
                'admin-programme-edit',
                ['programmeId' => (string)$programmeId],
            );
        }

        $this->programmeService->updateProgramme(
            $programme,
            $this->programmeDataFromRequest($request, $event, $start, $end),
        );
        $this->addUploadedImages($programme, $request);
        $this->warnAboutExclusiveConflicts($programme);
        $this->flashMessages->success('flash.success.programmeSaved');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    private function warnAboutExclusiveConflicts(Programme $programme): void
    {
        if ($programme->section->isExclusive === false || $programme->isAutoenrolled) {
            return;
        }

        $conflictCount = $this->programmeSectionRepository->countParticipantsWithOverlappingEnrolments(
            $programme->section,
        );
        if ($conflictCount > 0) {
            $this->flashMessages->warning(
                'flash.warning.programmeExclusiveConflicts',
                ['%count%' => (string)$conflictCount],
            );
        }
    }

    public function delete(Request $request, Response $response, Event $event, int $programmeId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $this->programmeService->deleteProgramme($programme);
        $this->flashMessages->success('flash.success.programmeDeleted');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    public function detail(Response $response, Event $event, int $programmeId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $enrolments = $this->programmeEnrolmentRepository->findActiveForProgramme($programme);

        $irregularEnrolmentIds = [];
        foreach ($enrolments as $enrolment) {
            $enrolmentParticipant = $enrolment->participant;
            $role = $enrolmentParticipant->role;
            if (
                $role === null
                || $programme->isForRole($role) === false
                || $enrolmentParticipant->getUserButNotNull()->status !== UserStatus::Paid
            ) {
                $irregularEnrolmentIds[] = $enrolment->id;
            }
        }

        return $this->view->render($response, 'admin/programmeDetail-admin.twig', [
            'programme' => $programme,
            'enrolments' => $enrolments,
            'occupancy' => $this->programmeRepository->getActiveOccupancy($event),
            'irregularEnrolmentIds' => $irregularEnrolmentIds,
            'hasImage' => $this->programmeImageRepository->findFirstForProgramme($programme) !== null,
            'event' => $event,
        ]);
    }

    public function assign(Request $request, Response $response, Event $event, int $programmeId): Response
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $participant = $this->participantRepository->findParticipantById(
            (int)$this->getParameterFromBody($request, 'participantId'),
            $event,
        );
        if ($participant === null) {
            $this->flashMessages->warning('flash.warning.programmeParticipantNotFound');

            return $this->redirectToDetail($request, $response, $programmeId);
        }

        $result = $this->programmeEnrolmentService->adminAssign($programme, $participant);
        $this->flash($result);

        return $this->redirectToDetail($request, $response, $programmeId);
    }

    public function remove(
        Request $request,
        Response $response,
        Event $event,
        int $programmeId,
        int $participantId,
    ): Response {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);
        $participant = $this->participantRepository->getParticipantById($participantId, $event);
        $result = $this->programmeEnrolmentService->adminRemove($programme, $participant);
        $this->flash($result);

        return $this->redirectToDetail($request, $response, $programmeId);
    }

    private function flash(ProgrammeEnrolmentResult $result): void
    {
        if ($result->error === null) {
            $this->flashMessages->success($result->flashKey());

            return;
        }

        $this->flashMessages->warning($result->flashKey());
    }

    private function renderForm(Response $response, Event $event, ?Programme $programme): Response
    {
        return $this->view->render($response, 'admin/programmeEdit-admin.twig', [
            'programme' => $programme,
            'sections' => $this->programmeSectionRepository->findForEvent($event),
            'places' => $this->programmePlaceRepository->findForEvent($event),
            'selectedPlaceId' => $programme?->programmePlace?->id,
            'images' => $programme === null ? [] : $this->programmeImageRepository->findForProgramme($programme),
            'allRoles' => ParticipantRole::all(),
            'event' => $event,
        ]);
    }

    private function programmeDataFromRequest(
        Request $request,
        Event $event,
        DateTimeImmutable $start,
        DateTimeImmutable $end,
    ): ProgrammeData {
        return new ProgrammeData(
            $this->getParameterFromBody($request, 'name'),
            $this->getParameterFromBody($request, 'description'),
            $start,
            $end,
            max(1, (int)$this->getParameterFromBody($request, 'capacity')),
            $this->isCheckboxCheckedInBody($request, 'isAutoenrolled'),
            $this->rolesFromRequest($request),
            $this->getSectionFromRequest($request, $event),
            // last, so that a refused form does not leave a newly created place behind
            $this->placeFromRequest($request, $event),
        );
    }

    private function placeFromRequest(Request $request, Event $event): ?ProgrammePlace
    {
        $newPlaceName = trim($this->getOptionalParameterFromBody($request, 'newPlaceName'));
        if ($newPlaceName !== '') {
            return $this->programmePlaceService->findOrCreateByName($event, $newPlaceName);
        }

        $placeId = $this->getOptionalParameterFromBody($request, 'placeId');
        if ($placeId === '') {
            return null;
        }

        return $this->programmePlaceRepository->getForEventById((int)$placeId, $event);
    }

    private function getSectionFromRequest(Request $request, Event $event): ProgrammeSection
    {
        return $this->programmeSectionRepository->getForEventById(
            (int)$this->getParameterFromBody($request, 'sectionId'),
            $event,
        );
    }

    private function parseDatetime(string $value): ?DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, new DateTimeZone(DateTimeUtils::TIMEZONE));

        if ($parsed === false || DateTimeImmutable::getLastErrors() !== false) {
            return null;
        }

        return $parsed;
    }

    /**
     * @return list<ParticipantRole>
     */
    private function rolesFromRequest(Request $request): array
    {
        return array_map(ParticipantRole::from(...), $this->getStringListFromBody($request, 'roles'));
    }

    /**
     * PHP drops the whole body of a POST over post_max_size, which several photos uploaded without
     * the resize script can reach, so the form fields arrive empty.
     */
    private function isBodyDroppedAsTooLarge(Request $request): bool
    {
        $parsedBody = $request->getParsedBody();
        if ($parsedBody !== null && $parsedBody !== []) {
            return false;
        }

        $postMaxSize = ini_parse_quantity((string)ini_get('post_max_size'));
        if ($postMaxSize <= 0 || (int)$request->getHeaderLine('Content-Length') <= $postMaxSize) {
            return false;
        }

        $this->flashMessages->error(
            'flash.error.programmeRequestTooLarge',
            ['%max%' => max(1, intdiv($postMaxSize, 1_048_576)) . ' MB'],
        );

        return true;
    }

    private function addUploadedImages(Programme $programme, Request $request): void
    {
        foreach ($this->uploadFileHandler->resolveUploadedImages($request, 'images') as $uploadedImage) {
            $refusal = $this->programmeImageService->addUpload($programme, $uploadedImage);
            if ($refusal === ProgrammeImageUploadRefusal::TooManyPixels) {
                $this->flashMessages->warning(
                    'flash.warning.programmeImageTooManyPixels',
                    ['%name%' => $uploadedImage->getClientFilename() ?? ''],
                );
            } elseif ($refusal === ProgrammeImageUploadRefusal::Undecodable) {
                $this->flashMessages->warning('flash.warning.invalidFileType');
            }
        }
    }

    private function redirectToDetail(Request $request, Response $response, int $programmeId): Response
    {
        return $this->redirect($request, $response, 'admin-programme-detail', ['programmeId' => (string)$programmeId]);
    }
}
