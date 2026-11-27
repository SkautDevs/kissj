<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Programme\DTO\ProgrammeSectionCountsDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeSectionAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeService $programmeService,
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
    ) {
    }

    public function overview(Response $response, Event $event): Response
    {
        $sections = $this->programmeSectionRepository->findForEvent($event);
        $countsBySection = $this->programmeRepository->countBySection($event);
        $counts = [];
        foreach ($sections as $section) {
            $counts[$section->id] = $countsBySection[$section->id] ?? new ProgrammeSectionCountsDTO(0, 0);
        }

        return $this->view->render($response, 'admin/programmeSections-admin.twig', [
            'sections' => $sections,
            'counts' => $counts,
            'event' => $event,
        ]);
    }

    public function create(Request $request, Response $response, Event $event): Response
    {
        $name = $this->nameFromRequest($request);
        if ($name === '') {
            return $this->redirectNameRequired($request, $response);
        }

        $this->programmeService->createSection(
            $event,
            $name,
            $this->subtitleFromRequest($request),
            $this->isCheckboxCheckedInBody($request, 'isExclusive'),
        );
        $this->flashMessages->success('flash.success.programmeSectionSaved');

        return $this->redirectToOverview($request, $response);
    }

    public function update(Request $request, Response $response, Event $event, int $sectionId): Response
    {
        $section = $this->programmeSectionRepository->getForEventById($sectionId, $event);

        $wasExclusive = $section->isExclusive;
        $name = $this->nameFromRequest($request);
        if ($name === '') {
            return $this->redirectNameRequired($request, $response);
        }
        $this->programmeService->updateSection(
            $section,
            $name,
            $this->subtitleFromRequest($request),
            $this->isCheckboxCheckedInBody($request, 'isExclusive'),
        );

        if ($wasExclusive === false && $section->isExclusive) {
            $conflictCount = $this->programmeEnrolmentRepository->countParticipantsWithOverlappingInSection($section);
            if ($conflictCount > 0) {
                $this->flashMessages->warning(
                    'flash.warning.programmeSectionExclusiveConflicts',
                    ['%count%' => (string)$conflictCount],
                );
            }
        }

        $this->flashMessages->success('flash.success.programmeSectionSaved');

        return $this->redirectToOverview($request, $response);
    }

    public function delete(Request $request, Response $response, Event $event, int $sectionId): Response
    {
        $section = $this->programmeSectionRepository->getForEventById($sectionId, $event);

        if ($this->programmeSectionRepository->deleteUnlessReferenced($section) === false) {
            $this->flashMessages->error(
                'flash.error.programmeSectionHasProgrammes',
                ['%count%' => (string)$this->programmeRepository->countIncludingDeletedForSection($section)],
            );

            return $this->redirectToOverview($request, $response);
        }
        $this->flashMessages->success('flash.success.programmeSectionDeleted');

        return $this->redirectToOverview($request, $response);
    }

    public function move(
        Request $request,
        Response $response,
        Event $event,
        int $sectionId,
        string $direction,
    ): Response {
        $this->programmeService->moveSection(
            $this->programmeSectionRepository->getForEventById($sectionId, $event),
            $direction,
        );

        return $this->redirectToOverview($request, $response);
    }

    private function nameFromRequest(Request $request): string
    {
        return trim($this->getParameterFromBody($request, 'name'));
    }

    private function subtitleFromRequest(Request $request): ?string
    {
        $subtitle = trim($this->getParameterFromBody($request, 'subtitle'));

        return $subtitle === '' ? null : $subtitle;
    }

    private function redirectNameRequired(Request $request, Response $response): Response
    {
        $this->flashMessages->error('flash.error.programmeSectionNameRequired');

        return $this->redirectToOverview($request, $response);
    }

    private function redirectToOverview(Request $request, Response $response): Response
    {
        return $this->redirect($request, $response, 'admin-programme-sections');
    }
}
