<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeImmutable;
use DateTimeZone;
use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FileHandler\UploadFileHandler;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class ProgrammeAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammeRepository $programmeRepository,
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
        $this->fillFromRequest($programme, $request);

        $this->programmeRepository->transactional(function () use ($programme): void {
            $this->programmeRepository->persist($programme);

            if ($programme->isPreregistered) {
                $this->programmeRegistrationRepository->softDeleteAllActiveForProgramme($programme);
            }
        });
        $this->saveImage($programme, $request);
        $this->flashMessages->success('flash.success.programmeSaved');

        return $this->redirect($request, $response, 'admin-programmes');
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
        $participant = $this->participantRepository->findParticipantById($participantId, $event);
        if ($participant === null) {
            $this->flashMessages->warning('flash.warning.programmeParticipantNotFound');

            return $this->redirectToDetail($request, $response, $programmeId);
        }

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
            'existingGroupNames' => $this->programmeRepository->findExclusiveGroupNames($event),
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

        $parsedBody = $request->getParsedBody();
        $programme->isPreregistered = is_array($parsedBody) && ($parsedBody['isPreregistered'] ?? '') === '1';

        $groupName = $this->getParameterFromBody($request, 'exclusiveGroupName');
        $programme->exclusiveGroupName = ($groupName === '' || $programme->isPreregistered) ? null : $groupName;
        $programme->setTargetRoles($this->rolesFromRequest($request));
    }

    private function parseDatetime(string $value): ?DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, new DateTimeZone('Europe/Berlin'));

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
        $parsedBody = $request->getParsedBody();
        $roleValues = is_array($parsedBody) && is_array($parsedBody['roles'] ?? null) ? $parsedBody['roles'] : [];
        $roles = [];
        foreach ($roleValues as $roleValue) {
            if (is_string($roleValue) === false) {
                continue;
            }
            $role = ParticipantRole::tryFrom($roleValue);
            if ($role === null) {
                continue;
            }
            $roles[] = $role;
        }

        return $roles;
    }

    private function saveImage(Programme $programme, Request $request): void
    {
        $uploadedFile = $this->uploadFileHandler->resolveUploadedFile($request, 'image');
        if (!$uploadedFile instanceof UploadedFile) {
            return;
        }

        if (!in_array($uploadedFile->getClientMediaType(), ['image/jpeg', 'image/png', 'image/gif'], true)) {
            $this->flashMessages->warning('flash.warning.invalidFileType');

            return;
        }

        $newFilename = bin2hex(random_bytes(16));
        $this->fileHandler->saveFile($uploadedFile, $newFilename);
        $programme->imageFilename = $newFilename;
        $this->programmeRepository->persist($programme);
    }

    private function redirectToDetail(Request $request, Response $response, int $programmeId): Response
    {
        return $this->redirect($request, $response, 'admin-programme-detail', ['programmeId' => (string)$programmeId]);
    }
}
