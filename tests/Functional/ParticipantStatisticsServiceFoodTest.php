<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\ParticipantStatisticsService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;
use Throwable;

class ParticipantStatisticsServiceFoodTest extends AppTestCase
{
    /** @var list<Participant> participants this test entered and that must be left again on teardown */
    private array $enteredParticipants = [];

    private ?ParticipantService $participantServiceForTeardown = null;

    protected function tearDown(): void
    {
        // The dev DB is never reset between runs, so any participant left "entered" here
        // would permanently inflate every future "currently present" query on its event.
        // This must run even when a test fails partway through, hence tearDown() rather
        // than a few lines at the end of each test method - and it must run before
        // parent::tearDown() closes the DB connection these calls rely on. setAsLeaved()
        // persists, which can genuinely throw, so the exception is caught and rethrown
        // after parent::tearDown() rather than using try/finally: PHPStan's
        // phpunit.callParent check only looks for a literal parent::tearDown() call as a
        // direct statement of this method, not one nested inside a finally block.
        $cleanupFailure = null;
        try {
            if ($this->participantServiceForTeardown !== null) {
                foreach ($this->enteredParticipants as $participant) {
                    $this->participantServiceForTeardown->setAsLeaved($participant);
                }
            }
        } catch (Throwable $throwable) {
            $cleanupFailure = $throwable;
        }

        parent::tearDown();

        if ($cleanupFailure !== null) {
            throw $cleanupFailure;
        }
    }

    private function enterAndTrackForCleanup(ParticipantService $participantService, Participant $participant): Participant
    {
        $participantService->setAsEntered($participant);
        $this->enteredParticipants[] = $participant;
        $this->participantServiceForTeardown = $participantService;

        return $participant;
    }

    public function testDayByDayFoodPlanIncludesOrganizingTeam(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getSmallTestEvent($eventRepository);

        $email = 'ot-food-plan-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $otParticipant = $userService->createParticipantSetRole($user, 'ot');
        $otParticipant->foodPreferences = 'detail.foodVegetarian';

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantRepository->persist($otParticipant);

        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        $plan = $statisticsService->createParticipantFoodPlanFromEvent($event, false);

        self::assertArrayHasKey('role.ot', $plan->roleAggregatedToArray()['rows']);
    }

    // Functional tests on this branch share a single, non-isolated dev database (each
    // getTestApp() migrate is a no-op against the real Postgres instance, not a fresh
    // per-test DB), so this test can't assert absolute counts; it snapshots the matrix
    // before and after creating its own fixtures and asserts on the deltas instead.
    public function testPresentFoodStatisticCountsOnlyCheckedInPaidParticipants(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getSmallTestEvent($eventRepository);
        $roles = ParticipantRole::all();

        $before = $statisticsService->getPresentFoodStatisticByRole($event, $roles);

        // 1) Paid, checked in, vegetarian IST -> should count
        $onSiteIst = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodVegetarian');
        $this->enterAndTrackForCleanup($participantService, $onSiteIst);

        // 2) Paid, checked in, food not set, patrol leader -> should count under "not set"
        $onSitePlNoFood = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'pl', null);
        $this->enterAndTrackForCleanup($participantService, $onSitePlNoFood);

        // 3) Paid, never checked in (entryDate null) -> excluded
        $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodVegetarian');

        // 4) Paid, checked in then left (leaveDate set) -> excluded
        $leftGuest = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'guest', 'detail.foodVegan');
        $this->enterAndTrackForCleanup($participantService, $leftGuest);
        $participantService->setAsLeaved($leftGuest);

        // 5) Checked in but Cancelled (not Paid) -> excluded
        $cancelledTl = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'tl', 'detail.foodVegetarian');
        $this->enterAndTrackForCleanup($participantService, $cancelledTl);
        $cancelledUser = $cancelledTl->getUserButNotNull();
        $cancelledUser->status = UserStatus::Cancelled;
        $userRepository->persist($cancelledUser);

        $after = $statisticsService->getPresentFoodStatisticByRole($event, $roles);

        self::assertContains('role.ist', $after['roles']);
        self::assertContains('role.pl', $after['roles']);
        self::assertContains('role.ot', $after['roles']);
        self::assertContains(ParticipantStatisticsService::NOT_SET_FOOD_KEY, $after['foodTypes']);

        self::assertSame(
            1,
            $this->matrixCell($after, 'role.ist', 'detail.foodVegetarian') - $this->matrixCell($before, 'role.ist', 'detail.foodVegetarian'),
        );
        self::assertSame(
            1,
            $this->matrixCell($after, 'role.pl', ParticipantStatisticsService::NOT_SET_FOOD_KEY) - $this->matrixCell($before, 'role.pl', ParticipantStatisticsService::NOT_SET_FOOD_KEY),
        );
        self::assertSame(
            0,
            $this->matrixCell($after, 'role.guest', 'detail.foodVegan') - $this->matrixCell($before, 'role.guest', 'detail.foodVegan'),
        );
        self::assertSame(
            0,
            $this->matrixCell($after, 'role.tl', 'detail.foodVegetarian') - $this->matrixCell($before, 'role.tl', 'detail.foodVegetarian'),
        );

        self::assertSame(1, $after['rowTotals']['role.ist'] - $before['rowTotals']['role.ist']);
        self::assertSame(1, $after['rowTotals']['role.pl'] - $before['rowTotals']['role.pl']);
        self::assertSame(
            1,
            $this->colTotal($after, 'detail.foodVegetarian') - $this->colTotal($before, 'detail.foodVegetarian'),
        );
        self::assertSame(
            1,
            $this->colTotal($after, ParticipantStatisticsService::NOT_SET_FOOD_KEY) - $this->colTotal($before, ParticipantStatisticsService::NOT_SET_FOOD_KEY),
        );
        self::assertSame(2, $after['grandTotal'] - $before['grandTotal']);
    }

    // ParticipantRepository::getAllParticipantsWithStatus() expands PatrolParticipant roles
    // with a second query joined through the patrol leader's user, so a patrol participant
    // whose own user is ALSO Paid is returned twice from that repository call. Without
    // deduping by id, the matrix would double-count this participant on check-in.
    public function testPresentFoodStatisticDedupesPatrolParticipantWithOwnPaidUser(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getSmallTestEvent($eventRepository);
        $roles = ParticipantRole::all();

        $before = $statisticsService->getPresentFoodStatisticByRole($event, $roles);

        $leaderEmail = 'present-food-dedupe-pl-' . bin2hex(random_bytes(4)) . '@example.com';
        $leaderUser = $userService->registerEmailUser($leaderEmail, $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');
        $leaderUser->status = UserStatus::Paid;
        $userRepository->persist($leaderUser);

        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);

        // the participant that would be double-counted: its own user is Paid, and it is
        // tied to a patrol leader whose user is also Paid
        $ppEmail = 'present-food-dedupe-pp-' . bin2hex(random_bytes(4)) . '@example.com';
        $ppUser = $userService->registerEmailUser($ppEmail, $event);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->user = $ppUser;
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->foodPreferences = 'detail.foodVegetarian';
        $participantRepository->persist($patrolParticipant);
        $ppUser->status = UserStatus::Paid;
        $userRepository->persist($ppUser);

        $this->enterAndTrackForCleanup($participantService, $patrolParticipant);

        $after = $statisticsService->getPresentFoodStatisticByRole($event, $roles);

        self::assertSame(
            1,
            $this->matrixCell($after, 'role.pp', 'detail.foodVegetarian') - $this->matrixCell($before, 'role.pp', 'detail.foodVegetarian'),
        );
        self::assertSame(1, $after['rowTotals']['role.pp'] - $before['rowTotals']['role.pp']);
        self::assertSame(
            1,
            $this->colTotal($after, 'detail.foodVegetarian') - $this->colTotal($before, 'detail.foodVegetarian'),
        );
        self::assertSame(1, $after['grandTotal'] - $before['grandTotal']);
    }

    // The literal 'detail.foodOther' is what EventType::getFoodOptions() actually stores, so
    // these fixtures use the string rather than Participant::FOOD_OTHER on purpose - the
    // constant is what is under test here.
    public function testOtherFoodDetailsListOnlyEnteredParticipants(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $enteredOther = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodOther');
        $this->enterAndTrackForCleanup($participantService, $enteredOther);

        $notEnteredOther = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodOther');

        $enteredVegetarian = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodVegetarian');
        $this->enterAndTrackForCleanup($participantService, $enteredVegetarian);

        $statistic = $statisticsService->getPresentFoodStatisticByRole($event, ParticipantRole::all());
        $listedIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $statistic['otherFoodParticipants'],
        );

        self::assertContains($enteredOther->id, $listedIds);
        self::assertNotContains($notEnteredOther->id, $listedIds);
        self::assertNotContains($enteredVegetarian->id, $listedIds);
    }

    public function testOtherFoodDetailsAreSortedByContingentThenName(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $noContingent = $this->makeEnteredOtherFoodParticipant($app, $event, null, 'Zeman', 'Adam');
        $bravoYoung = $this->makeEnteredOtherFoodParticipant($app, $event, 'bravo', 'Adamova', 'Bara');
        $alfaOlder = $this->makeEnteredOtherFoodParticipant($app, $event, 'alfa', 'Novak', 'Cyril');

        // passing the fixtures explicitly keeps the ordering assertion independent of whatever
        // else previous runs have left on the shared dev database
        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$noContingent, $bravoYoung, $alfaOlder],
            ParticipantRole::all(),
        );

        $orderedIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $statistic['otherFoodParticipants'],
        );

        self::assertSame([$alfaOlder->id, $bravoYoung->id, $noContingent->id], $orderedIds);
        self::assertTrue($statistic['showContingent']);
    }

    // Sharing a contingent isolates the assertion to the last-name tier of the sort.
    public function testOtherFoodDetailsSortSharedContingentByLastName(): void
    {
        $app = $this->getTestApp();
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $zeman = $this->makeEnteredOtherFoodParticipant($app, $event, 'gama', 'Zeman', 'Adam');
        $king = $this->makeEnteredOtherFoodParticipant($app, $event, 'gama', 'King', 'Filip');

        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$zeman, $king],
            ParticipantRole::all(),
        );

        $orderedIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $statistic['otherFoodParticipants'],
        );

        self::assertSame([$king->id, $zeman->id], $orderedIds);
    }

    public function testOtherFoodDetailsHideContingentWhenNobodyHasOne(): void
    {
        $app = $this->getTestApp();
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $withoutContingent = $this->makeEnteredOtherFoodParticipant($app, $event, null, 'Svoboda', 'Dan');

        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$withoutContingent],
            ParticipantRole::all(),
        );

        self::assertFalse($statistic['showContingent']);
        self::assertCount(1, $statistic['otherFoodParticipants']);

        $emptyStatistic = $statisticsService->getPresentFoodStatisticFromParticipants([], ParticipantRole::all());

        self::assertSame([], $emptyStatistic['otherFoodParticipants']);
        self::assertFalse($emptyStatistic['showContingent']);
    }

    public function testOtherFoodDetailsShowContingentInheritedFromPatrolLeader(): void
    {
        $app = $this->getTestApp();
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $patrolParticipant = $this->makeEnteredOtherFoodPatrolParticipant($app, $event, 'alfa', null, 'Dvorak', 'Emil');

        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$patrolParticipant],
            ParticipantRole::all(),
        );

        self::assertCount(1, $statistic['otherFoodParticipants']);
        self::assertNull($statistic['otherFoodParticipants'][0]->contingent);
        self::assertSame('alfa', $statistic['otherFoodParticipants'][0]->getOwnOrLeaderContingent());
        self::assertTrue($statistic['showContingent']);
    }

    public function testOtherFoodDetailsSortPatrolParticipantByInheritedContingent(): void
    {
        $app = $this->getTestApp();
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $bravoIst = $this->makeEnteredOtherFoodParticipant($app, $event, 'bravo', 'Adamova', 'Bara');
        $alfaPatrolParticipant = $this->makeEnteredOtherFoodPatrolParticipant($app, $event, 'alfa', null, 'Zeman', 'Adam');

        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$bravoIst, $alfaPatrolParticipant],
            ParticipantRole::all(),
        );

        $orderedIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $statistic['otherFoodParticipants'],
        );

        // sorted on the inherited 'alfa', even though the row's own contingent column is null -
        // by last name alone the order would be reversed
        self::assertSame([$alfaPatrolParticipant->id, $bravoIst->id], $orderedIds);
    }

    public function testOtherFoodDetailsPreferOwnContingentOverPatrolLeaders(): void
    {
        $app = $this->getTestApp();
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $this->getObrokTestEvent($app);

        $mikeIst = $this->makeEnteredOtherFoodParticipant($app, $event, 'mike', 'Adamova', 'Bara');
        $zuluPatrolParticipant = $this->makeEnteredOtherFoodPatrolParticipant($app, $event, 'alfa', 'zulu', 'Zeman', 'Adam');

        $statistic = $statisticsService->getPresentFoodStatisticFromParticipants(
            [$mikeIst, $zuluPatrolParticipant],
            ParticipantRole::all(),
        );

        $orderedIds = array_map(
            static fn (Participant $participant): int => $participant->id,
            $statistic['otherFoodParticipants'],
        );

        // the leader's 'alfa' would sort the patrol participant first; its own 'zulu' must win
        self::assertSame([$mikeIst->id, $zuluPatrolParticipant->id], $orderedIds);
    }

    /**
     * @param array{matrix: array<string, array<string, int>>, ...} $result
     */
    private function matrixCell(array $result, string $roleKey, string $foodKey): int
    {
        return $result['matrix'][$roleKey][$foodKey] ?? 0;
    }

    /**
     * colTotals is only zero-filled for food types present at computation time, so a key
     * expected in $after may genuinely be absent from the $before baseline.
     *
     * @param array{colTotals: array<string, int>, ...} $result
     */
    private function colTotal(array $result, string $foodKey): int
    {
        return $result['colTotals'][$foodKey] ?? 0;
    }

    private function makePaidParticipant(
        UserService $userService,
        UserRepository $userRepository,
        ParticipantRepository $participantRepository,
        Event $event,
        string $role,
        ?string $foodPreference,
    ): Participant {
        $email = 'present-food-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, $role);
        $participant->foodPreferences = $foodPreference;
        $participantRepository->persist($participant);

        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
    }

    /**
     * A patrol participant is the only role that has no contingent field of its own, so its value
     * has to come from the patrol leader.
     *
     * @param App<ContainerInterface> $app
     */
    private function makeEnteredOtherFoodPatrolParticipant(
        App $app,
        Event $event,
        ?string $leaderContingent,
        ?string $ownContingent,
        string $lastName,
        string $firstName,
    ): PatrolParticipant {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $suffix = bin2hex(random_bytes(4));

        $leaderUser = $userService->registerEmailUser('present-food-pl-' . $suffix . '@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');

        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);
        $patrolLeader->contingent = $leaderContingent;
        $patrolLeaderRepository->persist($patrolLeader);
        $leaderUser->status = UserStatus::Paid;
        $userRepository->persist($leaderUser);

        $ppUser = $userService->registerEmailUser('present-food-pp-' . $suffix . '@example.com', $event);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->user = $ppUser;
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->contingent = $ownContingent;
        $patrolParticipant->firstName = $firstName;
        $patrolParticipant->lastName = $lastName;
        $patrolParticipant->foodPreferences = 'detail.foodOther';
        $participantRepository->persist($patrolParticipant);
        $ppUser->status = UserStatus::Paid;
        $userRepository->persist($ppUser);

        $this->enterAndTrackForCleanup($participantService, $patrolParticipant);

        // same reason as in makeEnteredOtherFoodParticipant(): a hand-built entity is missing the
        // columns the statistics service reads, so hand back a freshly selected one
        $reloaded = $participantRepository->getParticipantById($patrolParticipant->id, $event);
        self::assertInstanceOf(PatrolParticipant::class, $reloaded);

        return $reloaded;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makeEnteredOtherFoodParticipant(
        App $app,
        Event $event,
        ?string $contingent,
        string $lastName,
        string $firstName,
    ): Participant {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);

        $participant = $this->makePaidParticipant(
            $userService,
            $userRepository,
            $participantRepository,
            $event,
            'ist',
            'detail.foodOther',
        );
        $participant->contingent = $contingent;
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participantRepository->persist($participant);
        $this->enterAndTrackForCleanup($participantService, $participant);

        // LeanMapper only populates the columns explicitly set on an entity before its
        // first persist(), so this hand-built object is missing 'leave_date' and would
        // throw when the statistics service reads it. Re-fetching gives the same shape
        // of entity production always passes in (a repository SELECT).
        return $participantRepository->getParticipantById($participant->id, $event);
    }
}
