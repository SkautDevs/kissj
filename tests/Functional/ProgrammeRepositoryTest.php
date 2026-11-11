<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
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

    public function testOccupancyRoleFilterAndSoftDelete(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);

        $istProgramme = $this->createProgramme($app, 'ist night game', null, [ParticipantRole::Ist]);
        $tlProgramme = $this->createProgramme(
            $app,
            'tl workshop',
            null,
            [ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant],
        );

        // functional tests share one persistent event; assert on the rows we created, not on event-global counts
        $istProgrammeIds = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveForRole($event, ParticipantRole::Ist),
        );
        self::assertContains($istProgramme->id, $istProgrammeIds);
        self::assertNotContains($tlProgramme->id, $istProgrammeIds);

        $user = $this->registerUser($app, 'programme-repo-test@example.com');
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $registration = new ProgrammeRegistration();
        $registration->programme = $istProgramme;
        $registration->participant = $participant;
        $registrationRepository->persist($registration);

        self::assertSame(1, $registrationRepository->countActiveForProgramme($istProgramme));
        $occupancy = $programmeRepository->getActiveOccupancy($event);
        self::assertSame(1, $occupancy[$istProgramme->id] ?? 0);
        self::assertArrayNotHasKey($tlProgramme->id, $occupancy);

        $registrationRepository->softDelete($registration);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($istProgramme));
        self::assertNull($registrationRepository->findActive($istProgramme, $participant));

        $istProgramme->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($istProgramme);
        $activeIstIdsAfterDelete = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActiveForRole($event, ParticipantRole::Ist),
        );
        self::assertNotContains($istProgramme->id, $activeIstIdsAfterDelete);
    }

    public function testFindActiveTargetsOwnRegistrationOnly(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);

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
            $registration = new ProgrammeRegistration();
            $registration->programme = $programme;
            $registration->participant = $participant;
            $registrationRepository->persist($registration);
        }

        $ownRegistration = $registrationRepository->findActive($programme, $ownParticipant);
        self::assertNotNull($ownRegistration);
        self::assertSame($ownParticipant->id, $ownRegistration->participant->id);

        $registrationRepository->softDelete($ownRegistration);
        self::assertNull($registrationRepository->findActive($programme, $ownParticipant));
        // the other participant's active registration is untouched by the targeted lookup
        self::assertNotNull($registrationRepository->findActive($programme, $otherParticipant));
    }

    public function testActiveLookupsSkipSoftDeletedRegistrations(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);

        $programme = $this->createProgramme($app, 'soft delete workshop', null, [ParticipantRole::Ist]);
        $participant = $userService->createParticipantSetRole(
            $this->registerUser($app, 'soft-delete-lookup@example.com'),
            'ist',
        );

        $first = new ProgrammeRegistration();
        $first->programme = $programme;
        $first->participant = $participant;
        $registrationRepository->persist($first);
        $registrationRepository->softDelete($first);

        $second = new ProgrammeRegistration();
        $second->programme = $programme;
        $second->participant = $participant;
        $registrationRepository->persist($second);

        $forParticipant = $registrationRepository->findActiveForParticipant($participant);
        self::assertCount(1, $forParticipant);
        self::assertSame($second->id, $forParticipant[0]->id);

        $forProgramme = $registrationRepository->findActiveForProgramme($programme);
        self::assertCount(1, $forProgramme);
        self::assertSame($second->id, $forProgramme[0]->id);

        $active = $registrationRepository->findActive($programme, $participant);
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
        $unregistered = $this->createProgramme($app, 'participant unregistered', null, [ParticipantRole::Ist]);
        $foreign = $this->createProgramme($app, 'participant foreign', null, [ParticipantRole::Ist]);

        $this->registerParticipant($app, $later, $participant);
        $this->registerParticipant($app, $earlier, $participant);
        $this->registerParticipant($app, $deleted, $participant);
        $this->getService($app, ProgrammeRegistrationRepository::class)
            ->softDelete($this->registerParticipant($app, $unregistered, $participant));
        $this->registerParticipant($app, $foreign, $otherParticipant);
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

    public function testFindActivePreregisteredForRoleFiltersInRepository(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);

        $istCeremony = $this->createProgramme($app, 'ist ceremony', null, [ParticipantRole::Ist], true);
        $istWorkshop = $this->createProgramme($app, 'ist workshop', null, [ParticipantRole::Ist]);
        $tlCeremony = $this->createProgramme($app, 'tl ceremony', null, [ParticipantRole::TroopLeader], true);

        $preregisteredIstIds = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActivePreregisteredForRole($event, ParticipantRole::Ist),
        );
        self::assertContains($istCeremony->id, $preregisteredIstIds);
        self::assertNotContains($istWorkshop->id, $preregisteredIstIds);
        self::assertNotContains($tlCeremony->id, $preregisteredIstIds);

        $istCeremony->deletedAt = DateTimeUtils::getDateTime();
        $programmeRepository->persist($istCeremony);
        $preregisteredIstIdsAfterDelete = array_map(
            static fn (Programme $programme): int => $programme->id,
            $programmeRepository->findActivePreregisteredForRole($event, ParticipantRole::Ist),
        );
        self::assertNotContains($istCeremony->id, $preregisteredIstIdsAfterDelete);
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
        bool $isPreregistered = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description of ' . $name;
        $programme->place = 'main field';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 10;
        $programme->isPreregistered = $isPreregistered;
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
    private function registerParticipant(
        App $app,
        Programme $programme,
        Participant $participant,
    ): ProgrammeRegistration {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->getService($app, ProgrammeRegistrationRepository::class)->persist($registration);

        return $registration;
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
