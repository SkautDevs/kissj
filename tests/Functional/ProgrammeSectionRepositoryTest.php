<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use RuntimeException;
use Tests\AppTestCase;

class ProgrammeSectionRepositoryTest extends AppTestCase
{
    public function testFindForEventIsOrderedAndScopedToEvent(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $otherEvent = $this->getSmallTestEvent($eventRepository);
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);

        $first = $this->createProgrammeSection($container, $event, 'first');
        $second = $this->createProgrammeSection($container, $event, 'second', true);
        $foreign = $this->createProgrammeSection($container, $otherEvent, 'foreign');
        $first->sortOrder = 5;
        $sectionRepository->persist($first);

        self::assertSame(
            [$second->id, $first->id],
            array_map(
                static fn (ProgrammeSection $section): int => $section->id,
                $sectionRepository->findForEvent($event),
            ),
        );
        self::assertSame($second->id, $sectionRepository->getForEventById($second->id, $event)->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Programme section not found for event');
        $sectionRepository->getForEventById($foreign->id, $event);
    }

    public function testCountProgrammesIncludesSoftDeletedAndActiveCountSkipsThem(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'counted');
        $empty = $this->createProgrammeSection($app->getContainer(), $event, 'empty');

        $active = $this->createProgramme($section);
        $deleted = $this->createProgramme($section);
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($active);
        $programmeRepository->persist($deleted);

        self::assertSame(2, $sectionRepository->countProgrammes($section));
        self::assertSame(0, $sectionRepository->countProgrammes($empty));
        $counts = $sectionRepository->countProgrammesBySection($event);
        self::assertSame(1, $counts[$section->id]->active);
        self::assertSame(1, $counts[$section->id]->deleted);
        self::assertArrayNotHasKey($empty->id, $counts);
    }

    public function testNextSortOrderFollowsTheHighestSectionOfTheEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getTestSlugEvent($eventRepository);
        $emptyEvent = $this->getTypedTestEvent($app, 'default', 'section-sort-order-empty-event');
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'sorted');
        $section->sortOrder = 41;
        $sectionRepository->persist($section);

        self::assertSame(42, $sectionRepository->getNextSortOrder($event));
        self::assertSame(0, $sectionRepository->getNextSortOrder($emptyEvent));
    }

    public function testDeleteUnlessReferencedKeepsSectionReferencedByProgramme(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $sectionRepository = $this->getService($app, ProgrammeSectionRepository::class);
        $referenced = $this->createProgrammeSection($app->getContainer(), $event, 'referenced');
        $empty = $this->createProgrammeSection($app->getContainer(), $event, 'deletable');
        $this->getService($app, ProgrammeRepository::class)->persist($this->createProgramme($referenced));

        self::assertFalse($sectionRepository->deleteUnlessReferenced($referenced));
        self::assertTrue($sectionRepository->deleteUnlessReferenced($empty));
        self::assertNotNull($sectionRepository->findOneBy(['id' => $referenced->id]));
        self::assertNull($sectionRepository->findOneBy(['id' => $empty->id]));
    }

    private function createProgramme(ProgrammeSection $section): Programme
    {
        $programme = new Programme();
        $programme->name = 'section counted programme';
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isPreregistered = false;
        $programme->event = $section->event;
        $programme->section = $section;
        $programme->setTargetRoles([ParticipantRole::Ist]);

        return $programme;
    }
}
