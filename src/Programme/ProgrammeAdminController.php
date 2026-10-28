<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeImmutable;
use DateTimeZone;
use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FileHandler\UploadFileHandler;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly EventRepository $eventRepository,
        private readonly UploadFileHandler $uploadFileHandler,
        private readonly ProgrammeRegistrationRepository $programmeRegistrationRepository,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function overview(Response $response, Event $event): Response
    {
        return $this->view->render($response, 'admin/programmes-admin.twig', [
            'programmes' => $this->programmeRepository->findActiveForEvent($event),
            'occupancy' => $this->programmeRepository->getActiveOccupancy($event),
            'event' => $event,
        ]);
    }

    public function saveWindow(Request $request, Response $response, Event $event): Response
    {
        $start = $this->getParameterFromBody($request, 'programmeRegistrationStart');
        $end = $this->getParameterFromBody($request, 'programmeRegistrationEnd');

        $startDate = $start === '' ? null : $this->parseDatetime($start);
        $endDate = $end === '' ? null : $this->parseDatetime($end);
        $isInvalid = ($start !== '' && $startDate === null)
            || ($end !== '' && $endDate === null)
            || ($startDate !== null && $endDate !== null && $endDate <= $startDate);
        if ($isInvalid) {
            $this->flashMessages->error('flash.error.programmeInvalidTime');

            return $this->redirect($request, $response, 'admin-programmes');
        }

        $event->programmeRegistrationStart = $startDate;
        $event->programmeRegistrationEnd = $endDate;
        $this->eventRepository->persist($event);
        $this->flashMessages->success('flash.success.programmeWindowSaved');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    public function showCreate(Response $response, Event $event): Response
    {
        return $this->renderForm($response, $event, null);
    }

    public function create(Request $request, Response $response, Event $event): Response
    {
        $start = $this->parseDatetime($this->getParameterFromBody($request, 'start'));
        $end = $this->parseDatetime($this->getParameterFromBody($request, 'end'));
        if ($start === null || $end === null || $end <= $start) {
            $this->flashMessages->error('flash.error.programmeInvalidTime');

            return $this->redirect($request, $response, 'admin-programme-new');
        }

        $programme = new Programme();
        $programme->event = $event;
        $programme->section = $this->getSectionFromRequest($request, $event);
        $programme->imageFilename = null;
        $programme->start = $start;
        $programme->end = $end;
        $this->fillFromRequest($programme, $request);

        $this->programmeRepository->persist($programme);
        $this->saveImage($programme, $request);
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

        $programme->start = $start;
        $programme->end = $end;
        $programme->section = $this->getSectionFromRequest($request, $event);
        $this->fillFromRequest($programme, $request);

        $this->programmeRepository->transactional(function () use ($programme): void {
            $this->programmeRepository->persist($programme);

            if ($programme->isPreregistered) {
                $this->programmeRegistrationRepository->softDeleteAllActiveForProgramme($programme);
            }
        });
        $this->saveImage($programme, $request);
        $this->warnAboutExclusiveConflicts($programme);
        $this->flashMessages->success('flash.success.programmeSaved');

        return $this->redirect($request, $response, 'admin-programmes');
    }

    private function warnAboutExclusiveConflicts(Programme $programme): void
    {
        if ($programme->section->isExclusive === false || $programme->isPreregistered) {
            return;
        }

        $conflictCount = $this->programmeSectionRepository->countParticipantsWithOverlappingRegistrations(
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
        $registrations = $this->programmeRegistrationRepository->findActiveForProgramme($programme);

        $irregularRegistrationIds = [];
        foreach ($registrations as $registration) {
            $registrationParticipant = $registration->participant;
            $role = $registrationParticipant->role;
            if (
                $role === null
                || $programme->isForRole($role) === false
                || $registrationParticipant->getUserButNotNull()->status !== UserStatus::Paid
            ) {
                $irregularRegistrationIds[] = $registration->id;
            }
        }

        return $this->view->render($response, 'admin/programmeDetail-admin.twig', [
            'programme' => $programme,
            'registrations' => $registrations,
            'occupancy' => $this->programmeRepository->getActiveOccupancy($event),
            'irregularRegistrationIds' => $irregularRegistrationIds,
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

        $result = $this->programmeService->adminAssign($programme, $participant);
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
        $result = $this->programmeService->adminRemove($programme, $participant);
        $this->flash($result);

        return $this->redirectToDetail($request, $response, $programmeId);
    }

    private function flash(ProgrammeRegistrationResult $result): void
    {
        if ($result->success) {
            $this->flashMessages->success($result->translationKey);

            return;
        }

        $this->flashMessages->warning($result->translationKey);
    }

    private function renderForm(Response $response, Event $event, ?Programme $programme): Response
    {
        return $this->view->render($response, 'admin/programmeEdit-admin.twig', [
            'programme' => $programme,
            'sections' => $this->programmeSectionRepository->findForEvent($event),
            'allRoles' => ParticipantRole::all(),
            'event' => $event,
        ]);
    }

    private function fillFromRequest(Programme $programme, Request $request): void
    {
        $programme->name = $this->getParameterFromBody($request, 'name');
        $programme->description = $this->getParameterFromBody($request, 'description');
        $programme->place = $this->getParameterFromBody($request, 'place');
        $programme->capacity = max(1, (int)$this->getParameterFromBody($request, 'capacity'));
        $programme->isPreregistered = $this->isCheckboxCheckedInBody($request, 'isPreregistered');

        $programme->setTargetRoles($this->rolesFromRequest($request));
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

    private function saveImage(Programme $programme, Request $request): void
    {
        $uploadedImage = $this->uploadFileHandler->resolveUploadedImage($request, 'image');
        if ($uploadedImage === null) {
            return;
        }

        $programme->imageFilename = $this->fileHandler->saveUploadedFile($uploadedImage);
        $this->programmeRepository->persist($programme);
    }

    private function redirectToDetail(Request $request, Response $response, int $programmeId): Response
    {
        return $this->redirect($request, $response, 'admin-programme-detail', ['programmeId' => (string)$programmeId]);
    }
}
