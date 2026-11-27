<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Place\ProgrammePlace;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeData;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeEnrolmentService;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeSectionRepository;
use kissj\Programme\ProgrammeService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeServiceTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testCreateProgrammeSavesEveryField(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $place = $this->createPlace($app, $event);

        $programme = $this->getService($app, ProgrammeService::class)->createProgramme(
            $event,
            new ProgrammeData(
                'created workshop',
                'created description',
                DateTimeUtils::getDateTime('2027-06-05 10:00'),
                DateTimeUtils::getDateTime('2027-06-05 12:30'),
                7,
                true,
                [ParticipantRole::Ist, ParticipantRole::Guest],
                $section,
                $place,
            ),
        );

        $reloaded = $this->reloadProgramme($app, $programme);
        self::assertSame($event->id, $reloaded->event->id);
        self::assertSame('created workshop', $reloaded->name);
        self::assertSame('created description', $reloaded->description);
        self::assertSame('2027-06-05 10:00', $reloaded->start->format('Y-m-d H:i'));
        self::assertSame('2027-06-05 12:30', $reloaded->end->format('Y-m-d H:i'));
        self::assertSame(7, $reloaded->capacity);
        self::assertTrue($reloaded->isAutoenrolled);
        self::assertSame([ParticipantRole::Ist, ParticipantRole::Guest], $reloaded->roles);
        self::assertSame($section->id, $reloaded->section->id);
        self::assertSame($place->id, $reloaded->programmePlace?->id);
    }

    public function testUpdateProgrammeToAutoenrolledChangesFieldsAndRemovesEnrolments(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $programme = $this->createProgramme($app, $event, 'updated workshop', $this->createPlace($app, $event));
        $ist = $this->createPaidIstOnEvent($app, $event, 'updated workshop ist');
        $this->getService($app, ProgrammeEnrolmentService::class)->adminAssign($programme, $ist);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        self::assertSame(1, $enrolmentRepository->countActiveForProgramme($programme));
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Ceremonies');

        $this->getService($app, ProgrammeService::class)->updateProgramme(
            $programme,
            new ProgrammeData(
                'renamed ceremony',
                'renamed description',
                DateTimeUtils::getDateTime('2027-06-06 08:00'),
                DateTimeUtils::getDateTime('2027-06-06 09:00'),
                40,
                true,
                [ParticipantRole::Guest],
                $section,
                null,
            ),
        );

        $reloaded = $this->reloadProgramme($app, $programme);
        self::assertSame('renamed ceremony', $reloaded->name);
        self::assertSame('renamed description', $reloaded->description);
        self::assertSame('2027-06-06 08:00', $reloaded->start->format('Y-m-d H:i'));
        self::assertSame('2027-06-06 09:00', $reloaded->end->format('Y-m-d H:i'));
        self::assertSame(40, $reloaded->capacity);
        self::assertTrue($reloaded->isAutoenrolled);
        self::assertSame([ParticipantRole::Guest], $reloaded->roles);
        self::assertSame($section->id, $reloaded->section->id);
        self::assertNull($reloaded->programmePlace);
        self::assertSame(0, $enrolmentRepository->countActiveForProgramme($programme));
    }

    public function testUpdateProgrammeToNotAutoenrolledKeepsEnrolments(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $programme = $this->createProgramme($app, $event, 'kept workshop');
        $ist = $this->createPaidIstOnEvent($app, $event, 'kept workshop ist');
        $this->getService($app, ProgrammeEnrolmentService::class)->adminAssign($programme, $ist);

        $this->getService($app, ProgrammeService::class)->updateProgramme(
            $programme,
            new ProgrammeData(
                'kept renamed',
                'description',
                DateTimeUtils::getDateTime('2027-06-05 10:00'),
                DateTimeUtils::getDateTime('2027-06-05 12:00'),
                5,
                false,
                [ParticipantRole::Ist],
                $programme->section,
                null,
            ),
        );

        self::assertFalse($this->reloadProgramme($app, $programme)->isAutoenrolled);
        self::assertSame(
            1,
            $this->getService($app, ProgrammeEnrolmentRepository::class)->countActiveForProgramme($programme),
        );
    }

    public function testSaveEnrolmentWindowSavesAndClears(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeService::class);

        $service->saveEnrolmentWindow(
            $this->getEvent($app),
            DateTimeUtils::getDateTime('2027-05-01 12:00'),
            DateTimeUtils::getDateTime('2027-05-20 18:00'),
        );

        $saved = $this->getEvent($app);
        self::assertSame('2027-05-01 12:00', $saved->programmeEnrolmentStart?->format('Y-m-d H:i'));
        self::assertSame('2027-05-20 18:00', $saved->programmeEnrolmentEnd?->format('Y-m-d H:i'));

        $service->saveEnrolmentWindow($saved, null, null);

        $cleared = $this->getEvent($app);
        self::assertNull($cleared->programmeEnrolmentStart);
        self::assertNull($cleared->programmeEnrolmentEnd);
    }

    public function testCreateSectionIsPlacedLast(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $this->createProgrammeSection($app->getContainer(), $event, 'Earlier');

        $section = $this->getService($app, ProgrammeService::class)->createSection($event, 'Last', 'evening', true);

        self::assertSame([$section->id], array_slice($this->sectionIds($app, $event), -1));
        $reloaded = $this->getService($app, ProgrammeSectionRepository::class)->getForEventById($section->id, $event);
        self::assertSame('Last', $reloaded->name);
        self::assertSame('evening', $reloaded->subtitle);
        self::assertTrue($reloaded->isExclusive);
    }

    public function testUpdateSectionChangesFields(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'Before', true);
        $section->subtitle = 'old subtitle';
        $this->getService($app, ProgrammeSectionRepository::class)->persist($section);

        $this->getService($app, ProgrammeService::class)->updateSection($section, 'After', null, false);

        $reloaded = $this->getService($app, ProgrammeSectionRepository::class)->getForEventById($section->id, $event);
        self::assertSame('After', $reloaded->name);
        self::assertNull($reloaded->subtitle);
        self::assertFalse($reloaded->isExclusive);
    }

    public function testMoveSectionSwapsWithNeighbour(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $upper = $this->createProgrammeSection($app->getContainer(), $event, 'Upper');
        $lower = $this->createProgrammeSection($app->getContainer(), $event, 'Lower');
        $before = $this->sectionIds($app, $event);
        self::assertSame([$upper->id, $lower->id], array_slice($before, -2));

        $this->getService($app, ProgrammeService::class)->moveSection($lower, 'up');

        self::assertSame([$lower->id, $upper->id], array_slice($this->sectionIds($app, $event), -2));
        self::assertSame(array_slice($before, 0, -2), array_slice($this->sectionIds($app, $event), 0, -2));
    }

    public function testMoveSectionAtEitherEdgeIsNoOp(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $this->createProgrammeSection($app->getContainer(), $event, 'Edge one');
        $this->createProgrammeSection($app->getContainer(), $event, 'Edge two');
        $service = $this->getService($app, ProgrammeService::class);
        $sections = $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event);
        $before = $this->sectionOrder($app, $event);

        $last = end($sections);
        self::assertNotFalse($last);

        $service->moveSection($sections[0], 'up');
        $service->moveSection($last, 'down');

        self::assertSame($before, $this->sectionOrder($app, $event));
    }

    public function testDeleteProgrammeCascades(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $enrolmentService = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, $this->getEvent($app), 'doomed');
        $ist = $this->createPaidIstOnEvent($app, $this->getEvent($app), 'doomed ist');
        self::assertNull($enrolmentService->enrol($ist, $programme->id)->error);

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);

        self::assertNotNull($this->fetchDeletedAt($app, 'programme', $programme->id));
        self::assertSame(
            0,
            $this->getService($app, ProgrammeEnrolmentRepository::class)->countActiveForProgramme($programme),
        );
    }

    public function testDeleteProgrammeIsLogged(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $programme = $this->createProgramme($app, $this->getEvent($app), 'logged doomed');
        $ist = $this->createPaidIstOnEvent($app, $this->getEvent($app), 'logged doomed ist');
        self::assertNull($this->getService($app, ProgrammeEnrolmentService::class)->enrol($ist, $programme->id)->error);

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);

        self::assertSame(
            [['programmeId' => $programme->id, 'enrolmentCount' => 1]],
            $this->getLoggedContexts('Deleted programme'),
        );
    }

    public function testFlippingProgrammeToAutoenrolledLogsRemovedEnrolmentsOnlyWhenThereWereAny(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $withEnrolment = $this->createProgramme($app, $event, 'logged flip with');
        $withoutEnrolment = $this->createProgramme($app, $event, 'logged flip without');
        $ist = $this->createPaidIstOnEvent($app, $event, 'logged flip ist');
        $this->getService($app, ProgrammeEnrolmentService::class)->adminAssign($withEnrolment, $ist);
        $service = $this->getService($app, ProgrammeService::class);

        foreach ([$withEnrolment, $withoutEnrolment] as $programme) {
            $service->updateProgramme(
                $programme,
                new ProgrammeData(
                    $programme->name,
                    'description',
                    DateTimeUtils::getDateTime('2027-06-05 10:00'),
                    DateTimeUtils::getDateTime('2027-06-05 12:00'),
                    5,
                    true,
                    [ParticipantRole::Ist],
                    $programme->section,
                    null,
                ),
            );
        }

        self::assertSame(
            [['programmeId' => $withEnrolment->id, 'enrolmentCount' => 1]],
            $this->getLoggedContexts('Removed enrolments of autoenrolled programme'),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(
        App $app,
        Event $event,
        string $name,
        ?ProgrammePlace $place = null,
    ): Programme {
        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->programmePlace = $place;
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPlace(App $app, Event $event): ProgrammePlace
    {
        return $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName(
            $event,
            'service place ' . bin2hex(random_bytes(4)),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function reloadProgramme(App $app, Programme $programme): Programme
    {
        $reloaded = $this->getService($app, ProgrammeRepository::class)->findOneBy(['id' => $programme->id]);
        self::assertInstanceOf(Programme::class, $reloaded);

        return $reloaded;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<int>
     */
    private function sectionIds(App $app, Event $event): array
    {
        return array_map(
            fn (ProgrammeSection $section): int => $section->id,
            $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<string>
     */
    private function sectionOrder(App $app, Event $event): array
    {
        return array_map(
            fn (ProgrammeSection $section): string => $section->id . ':' . $section->sortOrder,
            $this->getService($app, ProgrammeSectionRepository::class)->findForEvent($event),
        );
    }
}
