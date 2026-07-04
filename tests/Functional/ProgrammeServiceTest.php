<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeServiceTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testCapacityIsEnforced(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'small workshop', null, 1);

        $first = $this->createPaidIstWithEmail($app, 'first-ist@example.com');
        $second = $this->createPaidIstWithEmail($app, 'second-ist@example.com');

        $resultFirst = $service->register($first, $programme->id);
        self::assertTrue($resultFirst->success);
        self::assertSame(0, $resultFirst->remainingCapacity);

        $resultSecond = $service->register($second, $programme->id);
        self::assertFalse($resultSecond->success);
        self::assertSame('flash.error.programmeFull', $resultSecond->translationKey);
    }

    public function testDashboardProgrammesAreShownToPaidParticipant(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'dashboard workshop', null, 3);
        $preregistered = $this->createProgramme($app, 'dashboard ceremony', null, 3, isPreregistered: true);
        $ist = $this->createPaidIstWithEmail($app, 'dashboard-paid-ist@example.com');
        self::assertTrue($service->adminAssign($programme, $ist)->success);

        $registrations = $service->getDashboardRegistrations($ist);
        self::assertCount(1, $registrations);
        self::assertSame($programme->id, $registrations[0]->programme->id);
        self::assertContains(
            $preregistered->id,
            array_map(fn (Programme $p): int => $p->id, $service->getDashboardPreregisteredProgrammes($ist)),
        );
    }

    public function testDashboardProgrammesAreHiddenFromUnpaidParticipant(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $this->createProgramme($app, 'hidden ceremony', null, 3, isPreregistered: true);
        $user = $this->registerUser($app, 'dashboard-unpaid-ist@example.com');
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');

        self::assertSame([], $service->getDashboardRegistrations($ist));
        self::assertSame([], $service->getDashboardPreregisteredProgrammes($ist));
    }

    public function testUnregisterFreesSpotAndReRegisterWorks(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'reusable workshop', null, 1);
        $ist = $this->createPaidIstWithEmail($app, 'reuse-ist@example.com');

        self::assertTrue($service->register($ist, $programme->id)->success);
        self::assertTrue($service->unregister($ist, $programme->id)->success);
        self::assertTrue($service->register($ist, $programme->id)->success);
    }

    public function testDuplicateRegistrationRejected(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'once only', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'dup-ist@example.com');

        self::assertTrue($service->register($ist, $programme->id)->success);
        $duplicate = $service->register($ist, $programme->id);
        self::assertFalse($duplicate->success);
        self::assertSame('flash.error.programmeAlreadyRegistered', $duplicate->translationKey);
    }

    public function testSameGroupOverlapRejectedOthersAllowed(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $morning = $this->createProgramme($app, 'morning', 'workshops', 5, '2027-06-05 08:00', '2027-06-05 10:00');
        $overlapping = $this->createProgramme($app, 'overlapping', 'workshops', 5, '2027-06-05 09:00', '2027-06-05 11:00');
        $touching = $this->createProgramme($app, 'touching', 'workshops', 5, '2027-06-05 10:00', '2027-06-05 12:00');
        $otherGroup = $this->createProgramme($app, 'other group', 'games', 5, '2027-06-05 09:00', '2027-06-05 11:00');
        $ungrouped = $this->createProgramme($app, 'ungrouped', null, 5, '2027-06-05 09:00', '2027-06-05 11:00');

        $ist = $this->createPaidIstWithEmail($app, 'overlap-ist@example.com');

        self::assertTrue($service->register($ist, $morning->id)->success);

        $conflict = $service->register($ist, $overlapping->id);
        self::assertFalse($conflict->success);
        self::assertSame('flash.error.programmeConflict', $conflict->translationKey);

        self::assertTrue($service->register($ist, $touching->id)->success);
        self::assertTrue($service->register($ist, $otherGroup->id)->success);
        self::assertTrue($service->register($ist, $ungrouped->id)->success);
    }

    public function testWrongRoleRejected(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $tlOnly = $this->createProgramme($app, 'tl only', null, 5, '2027-06-05 08:00', '2027-06-05 10:00', [ParticipantRole::TroopLeader]);
        $ist = $this->createPaidIstWithEmail($app, 'wrong-role-ist@example.com');

        $result = $service->register($ist, $tlOnly->id);
        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeWrongRole', $result->translationKey);
    }

    public function testPreregisteredCannotBeRegistered(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $ceremony = $this->createProgramme($app, 'opening ceremony', null, 1, '2027-06-05 08:00', '2027-06-05 10:00', null, true);
        $ist = $this->createPaidIstWithEmail($app, 'ceremony-ist@example.com');

        $result = $service->register($ist, $ceremony->id);
        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotFound', $result->translationKey);
    }

    public function testPreregisteredDoesNotConflict(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        // same group + same time as ceremony - still must be registrable
        $this->createProgramme($app, 'ceremony', 'main', 1, '2027-06-05 08:00', '2027-06-05 12:00', null, true);
        $workshop = $this->createProgramme($app, 'workshop during ceremony', 'main', 5, '2027-06-05 09:00', '2027-06-05 10:00');
        $ist = $this->createPaidIstWithEmail($app, 'during-ceremony-ist@example.com');

        self::assertTrue($service->register($ist, $workshop->id)->success);
    }

    public function testAdminAssignBypassesEverythingAndRemoveWorks(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $full = $this->createProgramme($app, 'full one', null, 1);
        $blocker = $this->createPaidIstWithEmail($app, 'blocker-ist@example.com');
        self::assertTrue($service->register($blocker, $full->id)->success);

        $overridden = $this->createPaidIstWithEmail($app, 'override-ist@example.com');
        $assigned = $service->adminAssign($full, $overridden);
        self::assertTrue($assigned->success);
        self::assertSame('flash.success.programmeParticipantAssigned', $assigned->translationKey);
        self::assertSame(2, $registrationRepository->countActiveForProgramme($full));

        $removed = $service->adminRemove($full, $overridden);
        self::assertTrue($removed->success);
        self::assertSame('flash.success.programmeParticipantRemoved', $removed->translationKey);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($full));
    }

    public function testAdminAssignCalledTwiceIsIdempotent(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $programme = $this->createProgramme($app, 'double assign', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'double-assign-ist@example.com');

        $first = $service->adminAssign($programme, $ist);
        $second = $service->adminAssign($programme, $ist);

        self::assertTrue($first->success);
        self::assertFalse($second->success);
        self::assertSame('flash.warning.programmeAlreadyAssigned', $second->translationKey);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testAdminRemoveOfNotAssignedParticipantReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'remove unassigned', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'remove-unassigned-ist@example.com');

        $result = $service->adminRemove($programme, $ist);

        self::assertFalse($result->success);
        self::assertSame('flash.warning.programmeNotAssigned', $result->translationKey);
    }

    public function testAdminAssignOnPreregisteredProgrammeReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $ceremony = $this->createProgramme($app, 'admin-assign ceremony', null, 999, '2027-06-05 08:00', '2027-06-05 10:00', null, true);
        $ist = $this->createPaidIstWithEmail($app, 'admin-assign-ceremony-ist@example.com');

        $result = $service->adminAssign($ceremony, $ist);

        self::assertFalse($result->success);
        self::assertSame('flash.warning.programmePreregisteredNoAssign', $result->translationKey);
    }

    public function testAdminAssignOnCancelledParticipantReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $programme = $this->createProgramme($app, 'cancelled-assign workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'cancelled-assign-ist@example.com');

        $user = $ist->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $userRepository->persist($user);

        $result = $service->adminAssign($programme, $ist);

        self::assertFalse($result->success);
        self::assertSame('flash.warning.programmeCancelledNoAssign', $result->translationKey);
    }

    public function testAdminAssignOnProgrammeDeletedMeanwhileReturnsNotFound(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $programme = $this->createProgramme($app, 'deleted before assign', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-assign-ist@example.com');
        $staleProgramme = $programmeRepository->getForUpdate($programme->id);
        self::assertNotNull($staleProgramme);

        $service->deleteProgramme($programme);
        $result = $service->adminAssign($staleProgramme, $ist);

        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotFound', $result->translationKey);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testRegisterOnNonexistentProgrammeReturnsNotFoundWithoutThrowing(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $ist = $this->createPaidIstWithEmail($app, 'nonexistent-register-ist@example.com');

        $result = $service->register($ist, PHP_INT_MAX);

        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotFound', $result->translationKey);
    }

    public function testRegisterOnSoftDeletedProgrammeReturnsNotFound(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'deleted before register', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-register-ist@example.com');

        $service->deleteProgramme($programme);
        $result = $service->register($ist, $programme->id);

        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotFound', $result->translationKey);
    }

    public function testUnregisterOnNonexistentProgrammeReturnsNotFoundWithoutThrowing(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $ist = $this->createPaidIstWithEmail($app, 'nonexistent-unregister-ist@example.com');

        $result = $service->unregister($ist, PHP_INT_MAX);

        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotFound', $result->translationKey);
    }

    public function testUnregisterOnSoftDeletedProgrammeDoesNotThrow(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programme = $this->createProgramme($app, 'deleted before unregister', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-unregister-ist@example.com');
        self::assertTrue($service->register($ist, $programme->id)->success);

        // deleteProgramme() already cascades the soft-delete to active registrations, so
        // the registration is gone by the time unregister() runs - it correctly falls
        // through to "not registered" rather than needing an explicit isDeleted check
        $service->deleteProgramme($programme);
        $result = $service->unregister($ist, $programme->id);

        self::assertFalse($result->success);
        self::assertSame('flash.error.programmeNotRegistered', $result->translationKey);
    }

    public function testDeleteProgrammeCascades(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $programme = $this->createProgramme($app, 'doomed', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'doomed-ist@example.com');
        self::assertTrue($service->register($ist, $programme->id)->success);

        $service->deleteProgramme($programme);

        $reloaded = $programmeRepository->findOneBy(['id' => $programme->id]);
        self::assertInstanceOf(Programme::class, $reloaded);
        self::assertTrue($reloaded->isDeleted());
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testDeletingPatrolParticipantRemovesTheirRegistrations(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $patrolService = $this->getService($app, PatrolService::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $programme = $this->createProgramme(
            $app,
            'patrol member programme',
            null,
            5,
            roles: [ParticipantRole::PatrolParticipant],
        );
        $leaderParticipant = $this->createParticipantWithRole($app->getContainer(), 'pl');
        $patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->get($leaderParticipant->id);
        $patrolParticipant = $patrolService->createPatrolParticipant($patrolLeader);
        $patrolParticipantRepository->persist($patrolParticipant);
        $service->adminAssign($programme, $patrolParticipant);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));

        $patrolService->deletePatrolParticipant($patrolParticipant);

        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testCancelParticipantFreesProgrammes(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeService::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        $programme = $this->createProgramme($app, 'freed on cancel', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'cancelled-ist@example.com');
        self::assertTrue($service->register($ist, $programme->id)->success);

        $participantService->cancelParticipant($ist);

        self::assertCount(0, $registrationRepository->findActiveForParticipant($ist));
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole>|null $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?string $groupName,
        int $capacity,
        string $start = '2027-06-05 10:00',
        string $end = '2027-06-05 12:00',
        ?array $roles = null,
        bool $isPreregistered = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isPreregistered = $isPreregistered;
        $programme->exclusiveGroupName = $groupName;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->setTargetRoles($roles ?? [ParticipantRole::Ist, ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant]);
        $programmeRepository->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidIstWithEmail(App $app, string $email): Participant
    {
        $user = $this->registerUser($app, $email);
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $userRepository = $this->getService($app, UserRepository::class);
        $user = $userRepository->get($user->id);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
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
