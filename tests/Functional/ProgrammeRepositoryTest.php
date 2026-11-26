<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolment;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\User\User;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeRepositoryTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testOccupancyAutoenrolledRoleFilterAndSoftDelete(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);

        $istProgramme = $this->createProgramme($app, 'ist night game', null, [ParticipantRole::Ist], true);
        $tlProgramme = $this->createProgramme(
            $app,
            'tl workshop',
            null,
            [ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant],
            true,
        );

        // functional tests share one persistent event; assert on the rows we created, not on event-global counts
        $istProgrammeIds = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveAutoenrolledForRole($event, ParticipantRole::Ist),
        );
        self::assertContains($istProgramme->id, $istProgrammeIds);
        self::assertNotContains($tlProgramme->id, $istProgrammeIds);

        $user = $this->registerUser($app, 'programme-repo-test@example.com');
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $enrolment = new ProgrammeEnrolment();
        $enrolment->programme = $istProgramme;
        $enrolment->participant = $participant;
        $enrolmentRepository->persist($enrolment);

        self::assertSame(1, $enrolmentRepository->countActiveForProgramme($istProgramme));
        $occupancy = $programmeRepository->getActiveOccupancy($event);
        self::assertSame(1, $occupancy[$istProgramme->id] ?? 0);
        self::assertArrayNotHasKey($tlProgramme->id, $occupancy);

        $enrolmentRepository->softDelete($enrolment);
        self::assertSame(0, $enrolmentRepository->countActiveForProgramme($istProgramme));
        self::assertNull($enrolmentRepository->findActive($istProgramme, $participant));

        $istProgramme->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($istProgramme);
        $activeIstIdsAfterDelete = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveAutoenrolledForRole($event, ParticipantRole::Ist),
        );
        self::assertNotContains($istProgramme->id, $activeIstIdsAfterDelete);
    }

    public function testFindActiveTargetsOwnEnrolmentOnly(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);

        $programme = $this->createProgramme($app, 'find active workshop', null, [ParticipantRole::Ist]);

        $ownParticipant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'find-active-own@example.com'),
            'ist',
        );
        $otherParticipant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'find-active-other@example.com'),
            'ist',
        );

        foreach ([$ownParticipant, $otherParticipant] as $participant) {
            $enrolment = new ProgrammeEnrolment();
            $enrolment->programme = $programme;
            $enrolment->participant = $participant;
            $enrolmentRepository->persist($enrolment);
        }

        $ownEnrolment = $enrolmentRepository->findActive($programme, $ownParticipant);
        self::assertNotNull($ownEnrolment);
        self::assertSame($ownParticipant->id, $ownEnrolment->participant->id);

        $enrolmentRepository->softDelete($ownEnrolment);
        self::assertNull($enrolmentRepository->findActive($programme, $ownParticipant));
        // the other participant's active enrolment is untouched by the targeted lookup
        self::assertNotNull($enrolmentRepository->findActive($programme, $otherParticipant));
    }

    public function testActiveLookupsSkipSoftDeletedEnrolments(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);

        $programme = $this->createProgramme($app, 'soft delete workshop', null, [ParticipantRole::Ist]);
        $participant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'soft-delete-lookup@example.com'),
            'ist',
        );

        $first = new ProgrammeEnrolment();
        $first->programme = $programme;
        $first->participant = $participant;
        $enrolmentRepository->persist($first);
        $enrolmentRepository->softDelete($first);

        $second = new ProgrammeEnrolment();
        $second->programme = $programme;
        $second->participant = $participant;
        $enrolmentRepository->persist($second);

        $forParticipant = $enrolmentRepository->findActiveForParticipant($participant);
        self::assertCount(1, $forParticipant);
        self::assertSame($second->id, $forParticipant[0]->id);

        $forProgramme = $enrolmentRepository->findActiveForProgramme($programme);
        self::assertCount(1, $forProgramme);
        self::assertSame($second->id, $forProgramme[0]->id);

        $active = $enrolmentRepository->findActive($programme, $participant);
        self::assertNotNull($active);
        self::assertSame($second->id, $active->id);
    }

    public function testFindActiveForParticipantListsOwnActiveProgrammesByStart(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $participant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'find-for-participant-own@example.com'),
            'ist',
        );
        $otherParticipant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'find-for-participant-other@example.com'),
            'ist',
        );

        $later = $this->createProgramme($app, 'participant later', null, [ParticipantRole::Ist]);
        $earlier = $this->createProgramme($app, 'participant earlier', null, [ParticipantRole::Ist]);
        $earlier->start = DateTimeUtils::getDateTime('2027-06-05 08:00');
        $programmeRepository->persist($earlier);
        $deleted = $this->createProgramme($app, 'participant deleted', null, [ParticipantRole::Ist]);
        $withdrawn = $this->createProgramme($app, 'participant withdrawn', null, [ParticipantRole::Ist]);
        $foreign = $this->createProgramme($app, 'participant foreign', null, [ParticipantRole::Ist]);

        $this->enrolParticipant($app, $later, $participant);
        $this->enrolParticipant($app, $earlier, $participant);
        $this->enrolParticipant($app, $deleted, $participant);
        $this->getService($app, ProgrammeEnrolmentRepository::class)
            ->softDelete($this->enrolParticipant($app, $withdrawn, $participant));
        $this->enrolParticipant($app, $foreign, $otherParticipant);
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($deleted);

        self::assertSame(
            [$earlier->id, $later->id],
            array_map(
                static fn (Programme $programme): int => $programme->id,
                $programmeRepository->findActiveForParticipant($participant),
            ),
        );
    }

    public function testFindActiveAutoenrolledForRoleFiltersInRepository(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $istCeremony = $this->createProgramme($app, 'ist ceremony', null, [ParticipantRole::Ist], true);
        $istWorkshop = $this->createProgramme($app, 'ist workshop', null, [ParticipantRole::Ist]);
        $tlCeremony = $this->createProgramme($app, 'tl ceremony', null, [ParticipantRole::TroopLeader], true);

        $autoenrolledIstIds = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveAutoenrolledForRole($event, ParticipantRole::Ist),
        );
        self::assertContains($istCeremony->id, $autoenrolledIstIds);
        self::assertNotContains($istWorkshop->id, $autoenrolledIstIds);
        self::assertNotContains($tlCeremony->id, $autoenrolledIstIds);

        $istCeremony->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($istCeremony);
        $autoenrolledIstIdsAfterDelete = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveAutoenrolledForRole($event, ParticipantRole::Ist),
        );
        self::assertNotContains($istCeremony->id, $autoenrolledIstIdsAfterDelete);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole> $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?ProgrammeSection $section,
        array $roles,
        bool $isAutoenrolled = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description of ' . $name;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 10;
        $programme->isAutoenrolled = $isAutoenrolled;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->section = $section
            ?? $this->createProgrammeSection($app->getContainer(), $programme->event, 'Program');
        $programme->setTargetRoles($roles);
        $programmeRepository->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function enrolParticipant(
        App $app,
        Programme $programme,
        Participant $participant,
    ): ProgrammeEnrolment {
        $enrolment = new ProgrammeEnrolment();
        $enrolment->programme = $programme;
        $enrolment->participant = $participant;
        $this->getService($app, ProgrammeEnrolmentRepository::class)->persist($enrolment);

        return $enrolment;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerUser(App $app, string $email): User
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }
}
