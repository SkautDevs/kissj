<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolmentError;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeEnrolmentService;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\Programme\ProgrammeService;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeEnrolmentServiceTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testCapacityIsEnforced(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'small workshop', null, 1);

        $first = $this->createPaidIstWithEmail($app, 'first-ist@example.com');
        $second = $this->createPaidIstWithEmail($app, 'second-ist@example.com');

        $resultFirst = $service->enrol($first, $programme->id);
        self::assertNull($resultFirst->error);

        $resultSecond = $service->enrol($second, $programme->id);
        self::assertSame(ProgrammeEnrolmentError::Full, $resultSecond->error);
    }

    public function testDashboardProgrammesAreShownToPaidParticipant(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'dashboard workshop', null, 3);
        $autoenrolled = $this->createProgramme($app, 'dashboard ceremony', null, 3, isAutoenrolled: true);
        $ist = $this->createPaidIstWithEmail($app, 'dashboard-paid-ist@example.com');
        self::assertNull($service->adminAssign($programme, $ist)->error);

        $enrolments = $service->getDashboardEnrolments($ist);
        self::assertCount(1, $enrolments);
        self::assertSame($programme->id, $enrolments[0]->programme->id);
        self::assertContains(
            $autoenrolled->id,
            array_map(fn (Programme $p): int => $p->id, $service->getDashboardAutoenrolledProgrammes($ist)),
        );
    }

    public function testDashboardProgrammesAreHiddenFromUnpaidParticipant(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $this->createProgramme($app, 'hidden ceremony', null, 3, isAutoenrolled: true);
        $user = $this->registerUser($app, 'dashboard-unpaid-ist@example.com');
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');

        self::assertSame([], $service->getDashboardEnrolments($ist));
        self::assertSame([], $service->getDashboardAutoenrolledProgrammes($ist));
    }

    public function testWithdrawFreesSpotAndReEnrolWorks(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'reusable workshop', null, 1);
        $ist = $this->createPaidIstWithEmail($app, 'reuse-ist@example.com');

        self::assertNull($service->enrol($ist, $programme->id)->error);
        self::assertNull($service->withdraw($ist, $programme->id)->error);
        self::assertNull($service->enrol($ist, $programme->id)->error);
    }

    public function testEnrolLocksParticipantRow(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $programme = $this->createProgramme($app, 'locking workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'locking-ist@example.com');
        $participantRepository = $this->createMock(ParticipantRepository::class);
        $participantRepository->expects(self::once())->method('lock')->with($ist);
        $service = new ProgrammeEnrolmentService(
            $this->getService($app, ProgrammeRepository::class),
            $this->getService($app, ProgrammeEnrolmentRepository::class),
            $participantRepository,
            $this->getService($app, UserRepository::class),
            $this->getService($app, Metrics::class),
            $this->getService($app, LoggerInterface::class),
        );

        self::assertNull($service->enrol($ist, $programme->id)->error);
    }

    public function testEnrolRechecksPaidStatusFromDatabase(): void
    {
        $this->assertStaleStatusRefused(UserStatus::Open, 'stale-open-ist@example.com');
    }

    public function testEnrolOfCancelledParticipantRechecksStatusFromDatabase(): void
    {
        $this->assertStaleStatusRefused(UserStatus::Cancelled, 'stale-cancelled-ist@example.com');
    }

    public function testDuplicateEnrolmentRejected(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'once only', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'dup-ist@example.com');

        self::assertNull($service->enrol($ist, $programme->id)->error);
        $duplicate = $service->enrol($ist, $programme->id);
        self::assertSame(ProgrammeEnrolmentError::AlreadyEnrolled, $duplicate->error);
    }

    public function testSameExclusiveSectionOverlapRejectedOthersAllowed(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $workshops = $this->createProgrammeSection($app->getContainer(), $event, 'workshops', true);
        $games = $this->createProgrammeSection($app->getContainer(), $event, 'games', true);
        $open = $this->createProgrammeSection($app->getContainer(), $event, 'open');
        $morning = $this->createProgramme($app, 'morning', $workshops, 5, '2027-06-05 08:00', '2027-06-05 10:00');
        $overlapping = $this->createProgramme(
            $app,
            'overlapping',
            $workshops,
            5,
            '2027-06-05 09:00',
            '2027-06-05 11:00',
        );
        $touching = $this->createProgramme($app, 'touching', $workshops, 5, '2027-06-05 10:00', '2027-06-05 12:00');
        $otherGroup = $this->createProgramme($app, 'other group', $games, 5, '2027-06-05 09:00', '2027-06-05 11:00');
        $ungrouped = $this->createProgramme($app, 'ungrouped', $open, 5, '2027-06-05 09:00', '2027-06-05 11:00');
        $sameOpen = $this->createProgramme($app, 'same open', $open, 5, '2027-06-05 09:30', '2027-06-05 10:30');

        $ist = $this->createPaidIstWithEmail($app, 'overlap-ist@example.com');

        self::assertNull($service->enrol($ist, $morning->id)->error);

        $conflict = $service->enrol($ist, $overlapping->id);
        self::assertSame(ProgrammeEnrolmentError::Conflict, $conflict->error);

        self::assertNull($service->enrol($ist, $touching->id)->error);
        self::assertNull($service->enrol($ist, $otherGroup->id)->error);
        self::assertNull($service->enrol($ist, $ungrouped->id)->error);
        self::assertNull($service->enrol($ist, $sameOpen->id)->error);
    }

    public function testWrongRoleRejected(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $tlOnly = $this->createProgramme(
            $app,
            'tl only',
            null,
            5,
            '2027-06-05 08:00',
            '2027-06-05 10:00',
            [ParticipantRole::TroopLeader],
        );
        $ist = $this->createPaidIstWithEmail($app, 'wrong-role-ist@example.com');

        $result = $service->enrol($ist, $tlOnly->id);
        self::assertSame(ProgrammeEnrolmentError::WrongRole, $result->error);
    }

    public function testAutoenrolledCannotBeEnrolled(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $ceremony = $this->createProgramme(
            $app,
            'opening ceremony',
            null,
            1,
            '2027-06-05 08:00',
            '2027-06-05 10:00',
            null,
            true,
        );
        $ist = $this->createPaidIstWithEmail($app, 'ceremony-ist@example.com');

        $result = $service->enrol($ist, $ceremony->id);
        self::assertSame(ProgrammeEnrolmentError::NotPossible, $result->error);
    }

    public function testAutoenrolledCannotBeWithdrawn(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $ceremony = $this->createProgramme($app, 'closing ceremony', null, 1, isAutoenrolled: true);
        $ist = $this->createPaidIstWithEmail($app, 'ceremony-withdraw-ist@example.com');

        $result = $service->withdraw($ist, $ceremony->id);

        self::assertSame(ProgrammeEnrolmentError::NotPossible, $result->error);
    }

    public function testAutoenrolledDoesNotConflict(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $main = $this->createProgrammeSection($app->getContainer(), $event, 'main', true);
        // same exclusive section + same time as ceremony - still open for enrolment
        $this->createProgramme($app, 'ceremony', $main, 1, '2027-06-05 08:00', '2027-06-05 12:00', null, true);
        $workshop = $this->createProgramme(
            $app,
            'workshop during ceremony',
            $main,
            5,
            '2027-06-05 09:00',
            '2027-06-05 10:00',
        );
        $ist = $this->createPaidIstWithEmail($app, 'during-ceremony-ist@example.com');

        self::assertNull($service->enrol($ist, $workshop->id)->error);
    }

    public function testAdminAssignBypassesEverythingAndRemoveWorks(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $full = $this->createProgramme($app, 'full one', null, 1);
        $blocker = $this->createPaidIstWithEmail($app, 'blocker-ist@example.com');
        self::assertNull($service->enrol($blocker, $full->id)->error);

        $overridden = $this->createPaidIstWithEmail($app, 'override-ist@example.com');
        $assigned = $service->adminAssign($full, $overridden);
        self::assertNull($assigned->error);
        self::assertSame('flash.success.programmeParticipantAssigned', $assigned->flashKey());
        self::assertSame(2, $enrolmentRepository->countActiveForProgramme($full));

        $removed = $service->adminRemove($full, $overridden);
        self::assertNull($removed->error);
        self::assertSame('flash.success.programmeParticipantRemoved', $removed->flashKey());
        self::assertSame(1, $enrolmentRepository->countActiveForProgramme($full));
    }

    public function testAdminAssignCalledTwiceIsIdempotent(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $programme = $this->createProgramme($app, 'double assign', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'double-assign-ist@example.com');

        $first = $service->adminAssign($programme, $ist);
        $second = $service->adminAssign($programme, $ist);

        self::assertNull($first->error);
        self::assertSame('flash.warning.programmeAlreadyAssigned', $second->flashKey());
        self::assertSame(1, $enrolmentRepository->countActiveForProgramme($programme));
    }

    public function testAdminRemoveOfNotAssignedParticipantReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'remove unassigned', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'remove-unassigned-ist@example.com');

        $result = $service->adminRemove($programme, $ist);

        self::assertSame('flash.warning.programmeNotAssigned', $result->flashKey());
    }

    public function testAdminAssignOnAutoenrolledProgrammeReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $ceremony = $this->createProgramme(
            $app,
            'admin-assign ceremony',
            null,
            999,
            '2027-06-05 08:00',
            '2027-06-05 10:00',
            null,
            true,
        );
        $ist = $this->createPaidIstWithEmail($app, 'admin-assign-ceremony-ist@example.com');

        $result = $service->adminAssign($ceremony, $ist);

        self::assertSame('flash.warning.programmeAutoenrolledNoAssign', $result->flashKey());
    }

    public function testAdminAssignOnCancelledParticipantReturnsWarning(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $programme = $this->createProgramme($app, 'cancelled-assign workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'cancelled-assign-ist@example.com');

        $user = $ist->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $userRepository->persist($user);

        $result = $service->adminAssign($programme, $ist);

        self::assertSame('flash.warning.programmeCancelledNoAssign', $result->flashKey());
    }

    public function testAdminAssignRechecksCancelledStatusFromDatabase(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $programme = $this->createProgramme($app, 'stale assign workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'stale-assign-ist@example.com');
        $freshUser = $userRepository->get($ist->getUserButNotNull()->id);
        self::assertTrue($userRepository->claimStatusChange($freshUser, UserStatus::Paid, UserStatus::Cancelled));
        self::assertSame(UserStatus::Paid, $ist->getUserButNotNull()->status);

        $result = $service->adminAssign($programme, $ist);

        self::assertSame('flash.warning.programmeCancelledNoAssign', $result->flashKey());
        self::assertSame(0, $this->getService($app, ProgrammeEnrolmentRepository::class)
            ->countActiveForProgramme($programme));
    }

    public function testAdminAssignOnProgrammeDeletedMeanwhileReturnsNotFound(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $programme = $this->createProgramme($app, 'deleted before assign', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-assign-ist@example.com');
        $staleProgramme = $programmeRepository->get($programme->id);

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);
        $result = $service->adminAssign($staleProgramme, $ist);

        self::assertSame('flash.error.programmeNotFound', $result->flashKey());
        self::assertSame(0, $enrolmentRepository->countActiveForProgramme($programme));
    }

    public function testEnrolOnNonexistentProgrammeReturnsNotFoundWithoutThrowing(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $ist = $this->createPaidIstWithEmail($app, 'nonexistent-enrol-ist@example.com');

        $result = $service->enrol($ist, PHP_INT_MAX);

        self::assertSame('flash.error.programmeNotFound', $result->flashKey());
    }

    public function testEnrolOnSoftDeletedProgrammeReturnsNotFound(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'deleted before enrol', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-enrol-ist@example.com');

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);
        $result = $service->enrol($ist, $programme->id);

        self::assertSame('flash.error.programmeNotFound', $result->flashKey());
    }

    public function testWithdrawOnNonexistentProgrammeReturnsNotFoundWithoutThrowing(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $ist = $this->createPaidIstWithEmail($app, 'nonexistent-withdraw-ist@example.com');

        $result = $service->withdraw($ist, PHP_INT_MAX);

        self::assertSame('flash.error.programmeNotFound', $result->flashKey());
    }

    public function testWithdrawOnSoftDeletedProgrammeReturnsNotFound(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'deleted before withdraw', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'deleted-withdraw-ist@example.com');
        self::assertNull($service->enrol($ist, $programme->id)->error);

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);
        $result = $service->withdraw($ist, $programme->id);

        self::assertSame(ProgrammeEnrolmentError::NotFound, $result->error);
        self::assertSame('flash.error.programmeNotFound', $result->flashKey());
    }

    public function testUnpaidParticipantIsRefusedFirst(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $user = $this->registerUser($app, 'not-paid-ist@example.com');
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');

        foreach ([$service->enrol($ist, PHP_INT_MAX), $service->withdraw($ist, PHP_INT_MAX)] as $result) {
            self::assertSame('notPaid', $result->error?->value);
            self::assertSame(ProgrammeEnrolmentError::NotPaid, $result->error);
        }
    }

    public function testRefusedWritesAreCountedAsRefusals(): void
    {
        $app = $this->getTestApp();
        $user = $this->registerUser($app, 'counted-refusal-ist@example.com');
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');
        $metrics = $this->createMock(Metrics::class);
        $expectedActions = ['programmes.enrolled', 'programmes.withdrawn'];
        $metrics->expects(self::exactly(2))
            ->method('count')
            ->willReturnCallback(
                static function (MetricName $name, int $value, array $attributes) use (&$expectedActions): void {
                    self::assertSame('programmes.enrolment_refused', $name->value);
                    self::assertSame(1, $value);
                    self::assertSame(
                        [
                            'event' => self::TEST_EVENT_SLUG,
                            'error' => 'notPaid',
                            'action' => array_shift($expectedActions),
                        ],
                        $attributes,
                    );
                },
            );
        $service = new ProgrammeEnrolmentService(
            $this->getService($app, ProgrammeRepository::class),
            $this->getService($app, ProgrammeEnrolmentRepository::class),
            $this->getService($app, ParticipantRepository::class),
            $this->getService($app, UserRepository::class),
            $metrics,
            $this->getService($app, LoggerInterface::class),
        );

        $service->enrol($ist, PHP_INT_MAX);
        $service->withdraw($ist, PHP_INT_MAX);
    }

    public function testClosedWindowIsRefusedBeforeProgrammeLookup(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'programmeEnrolmentStart' => DateTimeUtils::getDateTime('-2 hours'),
            'programmeEnrolmentEnd' => DateTimeUtils::getDateTime('-1 hour'),
        ]);
        $ist = $this->createPaidIstWithEmail($app, 'closed-window-ist@example.com');

        foreach ([$service->enrol($ist, PHP_INT_MAX), $service->withdraw($ist, PHP_INT_MAX)] as $result) {
            self::assertSame('closed', $result->error?->value);
            self::assertSame(ProgrammeEnrolmentError::Closed, $result->error);
        }
    }

    public function testDeletingPatrolParticipantRemovesTheirEnrolments(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $patrolService = $this->getService($app, PatrolService::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
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
        self::assertSame(1, $enrolmentRepository->countActiveForProgramme($programme));

        $patrolService->deletePatrolParticipant($patrolParticipant);

        self::assertSame(0, $enrolmentRepository->countActiveForProgramme($programme));
    }

    public function testCancelParticipantFreesProgrammes(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $programme = $this->createProgramme($app, 'freed on cancel', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'cancelled-ist@example.com');
        self::assertNull($service->enrol($ist, $programme->id)->error);

        $participantService->cancelParticipant($ist);

        self::assertCount(0, $enrolmentRepository->findActiveForParticipant($ist));
    }

    public function testEnrolAndWithdrawAreLogged(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'logged workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'logged-ist@example.com');
        $expectedContext = ['programmeId' => $programme->id, 'participantId' => $ist->id];

        $service->enrol($ist, $programme->id);
        self::assertSame([$expectedContext], $this->getLoggedContexts('Enrolled participant in programme'));

        $service->withdraw($ist, $programme->id);
        self::assertSame([$expectedContext], $this->getLoggedContexts('Withdrew participant from programme'));
    }

    public function testAdminAssignAndRemoveAreLogged(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'admin logged workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'admin-logged-ist@example.com');
        $expectedContext = ['programmeId' => $programme->id, 'participantId' => $ist->id];

        $service->adminAssign($programme, $ist);
        self::assertSame([$expectedContext], $this->getLoggedContexts('Assigned participant to programme'));

        $service->adminRemove($programme, $ist);
        self::assertSame([$expectedContext], $this->getLoggedContexts('Removed participant from programme'));
    }

    public function testRefusedEnrolmentChangesAreNotLogged(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'refused logged workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'refused-logged-ist@example.com');

        $service->withdraw($ist, $programme->id);
        $service->adminRemove($programme, $ist);
        $service->adminAssign($programme, $ist);
        $service->adminAssign($programme, $ist);

        self::assertSame(
            [['programmeId' => $programme->id, 'participantId' => $ist->id]],
            $this->getLoggedContexts('Assigned participant to programme'),
        );
        self::assertSame([], $this->getLoggedContexts('Withdrew participant from programme'));
        self::assertSame([], $this->getLoggedContexts('Removed participant from programme'));
    }

    public function testFreeAllProgrammesLogsOnlyWhenSomethingWasFreed(): void
    {
        $app = $this->getTestApp();
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $first = $this->createProgramme($app, 'freed logged first', null, 5);
        $second = $this->createProgramme($app, 'freed logged second', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'freed-logged-ist@example.com');
        $service->adminAssign($first, $ist);
        $service->adminAssign($second, $ist);

        $service->freeAllProgrammes($ist);
        $service->freeAllProgrammes($ist);

        self::assertSame(
            [['participantId' => $ist->id, 'enrolmentCount' => 2]],
            $this->getLoggedContexts('Freed programme enrolments of participant'),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole>|null $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?ProgrammeSection $section,
        int $capacity,
        string $start = '2027-06-05 10:00',
        string $end = '2027-06-05 12:00',
        ?array $roles = null,
        bool $isAutoenrolled = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isAutoenrolled = $isAutoenrolled;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->section = $section
            ?? $this->createProgrammeSection($app->getContainer(), $programme->event, 'Program');
        $programme->roles = $roles
            ?? [ParticipantRole::Ist, ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant];
        $programmeRepository->persist($programme);

        return $programme;
    }

    private function assertStaleStatusRefused(UserStatus $databaseStatus, string $email): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $service = $this->getService($app, ProgrammeEnrolmentService::class);
        $programme = $this->createProgramme($app, 'stale workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, $email);
        $userRepository = $this->getService($app, UserRepository::class);
        $freshUser = $userRepository->get($ist->getUserButNotNull()->id);
        self::assertTrue($userRepository->claimStatusChange($freshUser, UserStatus::Paid, $databaseStatus));
        self::assertNotSame($freshUser, $ist->getUserButNotNull());
        self::assertSame(UserStatus::Paid, $ist->getUserButNotNull()->status);

        $result = $service->enrol($ist, $programme->id);

        self::assertSame('notPaid', $result->error?->value);
        self::assertSame(0, $this->getService($app, ProgrammeEnrolmentRepository::class)
            ->countActiveForProgramme($programme));
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
