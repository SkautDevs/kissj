<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Programme\DTO\ProgrammeSectionCountsDTO;
use LogicException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeSectionAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeSectionRepository $programmeSectionRepository,
    ) {
    }

    public function overview(Response $response, Event $event): Response
    {
        $sections = $this->programmeSectionRepository->findForEvent($event);
        $countsBySection = $this->programmeSectionRepository->countProgrammesBySection($event);
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
        $section = new ProgrammeSection();
        $section->event = $event;
        $section->sortOrder = $this->programmeSectionRepository->getNextSortOrder($event);
        if ($this->fillFromRequest($section, $request) === false) {
            return $this->redirectNameRequired($request, $response);
        }

        $this->programmeSectionRepository->persist($section);
        $this->flashMessages->success('flash.success.programmeSectionSaved');

        return $this->redirectToOverview($request, $response);
    }

    public function update(Request $request, Response $response, Event $event, int $sectionId): Response
    {
        $section = $this->programmeSectionRepository->getForEventById($sectionId, $event);

        $wasExclusive = $section->isExclusive;
        if ($this->fillFromRequest($section, $request) === false) {
            return $this->redirectNameRequired($request, $response);
        }
        $this->programmeSectionRepository->persist($section);

        if ($wasExclusive === false && $section->isExclusive) {
            $conflictCount = $this->programmeSectionRepository->countParticipantsWithOverlappingRegistrations($section);
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
                ['%count%' => (string)$this->programmeSectionRepository->countProgrammes($section)],
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
        $section = $this->programmeSectionRepository->getForEventById($sectionId, $event);

        $sections = $this->programmeSectionRepository->findForEvent($event);
        $position = $this->positionOf($sections, $section);
        $neighbourPosition = $direction === 'up' ? $position - 1 : $position + 1;
        if (isset($sections[$neighbourPosition]) === false) {
            return $this->redirectToOverview($request, $response);
        }

        $sections[$position] = $sections[$neighbourPosition];
        $sections[$neighbourPosition] = $section;
        $this->programmeSectionRepository->transactional(function () use ($sections): void {
            foreach ($sections as $index => $orderedSection) {
                if ($orderedSection->sortOrder === $index) {
                    continue;
                }

                $orderedSection->sortOrder = $index;
                $this->programmeSectionRepository->persist($orderedSection);
            }
        });

        return $this->redirectToOverview($request, $response);
    }

    /**
     * @param list<ProgrammeSection> $sections
     */
    private function positionOf(array $sections, ProgrammeSection $section): int
    {
        foreach ($sections as $position => $candidate) {
            if ($candidate->id === $section->id) {
                return $position;
            }
        }

        throw new LogicException('Section ' . $section->id . ' is missing in its own event');
    }

    private function fillFromRequest(ProgrammeSection $section, Request $request): bool
    {
        $name = trim($this->getParameterFromBody($request, 'name'));
        if ($name === '') {
            return false;
        }
        $subtitle = trim($this->getParameterFromBody($request, 'subtitle'));

        $section->name = $name;
        $section->subtitle = $subtitle === '' ? null : $subtitle;
        $section->isExclusive = $this->isCheckboxCheckedInBody($request, 'isExclusive');

        return true;
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
