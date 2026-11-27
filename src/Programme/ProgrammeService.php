<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeImmutable;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use LogicException;

readonly class ProgrammeService
{
    public function __construct(
        private ProgrammeRepository $programmeRepository,
        private ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
        private ProgrammeSectionRepository $programmeSectionRepository,
        private EventRepository $eventRepository,
    ) {
    }

    public function createProgramme(Event $event, ProgrammeData $data): Programme
    {
        $programme = new Programme();
        $programme->event = $event;
        $this->fillProgramme($programme, $data);
        $this->programmeRepository->persist($programme);

        return $programme;
    }

    public function updateProgramme(Programme $programme, ProgrammeData $data): void
    {
        $this->fillProgramme($programme, $data);
        $this->programmeRepository->transactional(function () use ($programme): void {
            $this->programmeRepository->persist($programme);

            if ($programme->isAutoenrolled) {
                $this->programmeEnrolmentRepository->softDeleteAllActiveForProgramme($programme);
            }
        });
    }

    public function deleteProgramme(Programme $programme): void
    {
        $this->programmeRepository->transactional(
            function () use ($programme): void {
                $this->programmeEnrolmentRepository->softDeleteAllActiveForProgramme($programme);
                $programme->deletedAt = DateTimeUtils::getDateTime();
                $this->programmeRepository->persist($programme);
            },
        );
    }

    public function saveEnrolmentWindow(Event $event, ?DateTimeImmutable $start, ?DateTimeImmutable $end): void
    {
        $event->programmeEnrolmentStart = $start;
        $event->programmeEnrolmentEnd = $end;
        $this->eventRepository->persist($event);
    }

    public function createSection(Event $event, string $name, ?string $subtitle, bool $isExclusive): ProgrammeSection
    {
        $section = new ProgrammeSection();
        $section->event = $event;
        $section->sortOrder = $this->programmeSectionRepository->getNextSortOrder($event);
        $this->fillSection($section, $name, $subtitle, $isExclusive);
        $this->programmeSectionRepository->persist($section);

        return $section;
    }

    public function updateSection(ProgrammeSection $section, string $name, ?string $subtitle, bool $isExclusive): void
    {
        $this->fillSection($section, $name, $subtitle, $isExclusive);
        $this->programmeSectionRepository->persist($section);
    }

    public function moveSection(ProgrammeSection $section, string $direction): void
    {
        $sections = $this->programmeSectionRepository->findForEvent($section->event);
        $position = $this->positionOf($sections, $section);
        $neighbourPosition = $direction === 'up' ? $position - 1 : $position + 1;
        if (isset($sections[$neighbourPosition]) === false) {
            return;
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
    }

    private function fillProgramme(Programme $programme, ProgrammeData $data): void
    {
        $programme->name = $data->name;
        $programme->description = $data->description;
        $programme->start = $data->start;
        $programme->end = $data->end;
        $programme->capacity = $data->capacity;
        $programme->isAutoenrolled = $data->isAutoenrolled;
        $programme->roles = $data->roles;
        $programme->section = $data->section;
        $programme->programmePlace = $data->place;
    }

    private function fillSection(ProgrammeSection $section, string $name, ?string $subtitle, bool $isExclusive): void
    {
        $section->name = $name;
        $section->subtitle = $subtitle;
        $section->isExclusive = $isExclusive;
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
}
