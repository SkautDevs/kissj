<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Orm\Mapper;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolment;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use LeanMapper\Caller;
use LeanMapper\ImplicitFilters;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Tests\AppTestCase;

class SoftDeleteFilterTest extends AppTestCase
{
    public function testProgrammeRepositorySkipsDeletedProgramme(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $section = $this->createProgrammeSection($app->getContainer(), $event, 'filtered programmes');
        $live = $this->createProgramme($app, $event, $section, 'live programme');
        $deleted = $this->createProgramme($app, $event, $section, 'deleted programme');
        $this->markProgrammeDeleted($app, $deleted);
        $repository = $this->getService($app, ProgrammeRepository::class);

        self::assertSame(
            [$live->id],
            array_map(
                static fn (Programme $programme): int => $programme->id,
                $repository->findBy(['section_id' => $section->id]),
            ),
        );
        self::assertNull($repository->findOneBy(['id' => $deleted->id]));
        self::assertSame($live->id, $repository->findOneBy(['id' => $live->id])?->id);
        self::assertFalse($repository->isExisting(['id' => $deleted->id]));
        self::assertTrue($repository->isExisting(['id' => $live->id]));
        self::assertSame(1, $repository->countBy(['section_id' => $section->id]));
        self::assertNull($repository->transactional(fn (): ?Programme => $repository->findWithLock($deleted->id)));
        self::assertSame(
            $live->id,
            $repository->transactional(fn (): ?Programme => $repository->findWithLock($live->id))?->id,
        );
        self::assertSame($live->id, $repository->get($live->id)->id);

        $this->expectException(RuntimeException::class);
        $repository->get($deleted->id);
    }

    public function testEnrolmentRepositorySkipsDeletedEnrolment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $programme = $this->createProgramme(
            $app,
            $event,
            $this->createProgrammeSection($app->getContainer(), $event, 'filtered enrolments'),
            'enrolled programme',
        );
        $repository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $live = $repository->create($programme, $this->createPaidIstOnEvent($app, $event, 'live enrolment ist'));
        $deleted = $repository->create($programme, $this->createPaidIstOnEvent($app, $event, 'deleted enrolment ist'));
        $repository->softDelete($deleted, DateTimeUtils::getDateTime());

        self::assertSame(
            [$live->id],
            array_map(
                static fn (ProgrammeEnrolment $enrolment): int => $enrolment->id,
                $repository->findBy(['programme_id' => $programme->id]),
            ),
        );
        self::assertNull($repository->findOneBy(['id' => $deleted->id]));
        self::assertSame($live->id, $repository->findOneBy(['id' => $live->id])?->id);
        self::assertFalse($repository->isExisting(['id' => $deleted->id]));
        self::assertTrue($repository->isExisting(['id' => $live->id]));
        self::assertSame(1, $repository->countBy(['programme_id' => $programme->id]));
        self::assertNull(
            $repository->transactional(fn (): ?ProgrammeEnrolment => $repository->findWithLock($deleted->id)),
        );
        self::assertSame(
            $live->id,
            $repository->transactional(fn (): ?ProgrammeEnrolment => $repository->findWithLock($live->id))?->id,
        );
        self::assertSame($live->id, $repository->get($live->id)->id);

        $this->expectException(RuntimeException::class);
        $repository->get($deleted->id);
    }

    public function testLiveEnrolmentStillReachesItsDeletedProgramme(): void
    {
        $app = $this->getTestApp();
        $event = $this->getEvent($app);
        $programme = $this->createProgramme(
            $app,
            $event,
            $this->createProgrammeSection($app->getContainer(), $event, 'traversed section'),
            'traversed programme',
        );
        $repository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $enrolment = $repository->create($programme, $this->createPaidIstOnEvent($app, $event, 'traversing ist'));
        $this->markProgrammeDeleted($app, $programme);

        self::assertSame($programme->id, $repository->get($enrolment->id)->programme->id);
    }

    public function testFilterAppliesOnlyToSoftDeletableEntitiesLoadedByRepository(): void
    {
        $app = $this->getTestApp();
        $mapper = new Mapper();

        $programmeFilters = $mapper->getImplicitFilters(
            Programme::class,
            new Caller($this->getService($app, ProgrammeRepository::class)),
        );
        self::assertInstanceOf(ImplicitFilters::class, $programmeFilters);
        self::assertCount(1, $programmeFilters->getFilters());

        $enrolmentFilters = $mapper->getImplicitFilters(
            ProgrammeEnrolment::class,
            new Caller($this->getService($app, ProgrammeEnrolmentRepository::class)),
        );
        self::assertInstanceOf(ImplicitFilters::class, $enrolmentFilters);
        self::assertCount(1, $enrolmentFilters->getFilters());

        self::assertSame(
            [],
            $mapper->getImplicitFilters(
                Participant::class,
                new Caller($this->getService($app, ParticipantRepository::class)),
            ),
        );
        self::assertSame([], $mapper->getImplicitFilters(Programme::class, new Caller(new ProgrammeEnrolment())));
        self::assertSame([], $mapper->getImplicitFilters(Programme::class));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getEvent(App $app): Event
    {
        return $this->getService($app, EventRepository::class)->getOneBy(['slug' => 'test-event-slug']);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createProgramme(App $app, Event $event, ProgrammeSection $section, string $name): Programme
    {
        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $section;
        $programme->programmePlace = null;
        $programme->roles = [ParticipantRole::Ist];
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function markProgrammeDeleted(App $app, Programme $programme): void
    {
        $programme->deletedAt = DateTimeUtils::getDateTime();
        $this->getService($app, ProgrammeRepository::class)->persist($programme);
    }
}
