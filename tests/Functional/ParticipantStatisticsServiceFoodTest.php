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
use Tests\AppTestCase;

class ParticipantStatisticsServiceFoodTest extends AppTestCase
{
    public function testDayByDayFoodPlanIncludesOrganizingTeam(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $eventRepository->get(1);

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
    // per-test DB). Event 1 already holds thousands of seeded on-site participants,
    // so this test can't assert absolute counts; it snapshots the matrix before and
    // after creating its own fixtures and asserts on the deltas instead.
    public function testPresentFoodStatisticCountsOnlyCheckedInPaidParticipants(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $statisticsService = $this->getService($app, ParticipantStatisticsService::class);

        $event = $eventRepository->get(1);
        $roles = ParticipantRole::all();

        $before = $statisticsService->getPresentFoodStatisticByRole($event, $roles);

        // 1) Paid, checked in, vegetarian IST -> should count
        $onSiteIst = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodVegetarian');
        $participantService->setAsEntered($onSiteIst);

        // 2) Paid, checked in, food not set, patrol leader -> should count under "not set"
        $onSitePlNoFood = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'pl', null);
        $participantService->setAsEntered($onSitePlNoFood);

        // 3) Paid, never checked in (entryDate null) -> excluded
        $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'ist', 'detail.foodVegetarian');

        // 4) Paid, checked in then left (leaveDate set) -> excluded
        $leftGuest = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'guest', 'detail.foodVegan');
        $participantService->setAsEntered($leftGuest);
        $participantService->setAsLeaved($leftGuest);

        // 5) Checked in but Cancelled (not Paid) -> excluded
        $cancelledTl = $this->makePaidParticipant($userService, $userRepository, $participantRepository, $event, 'tl', 'detail.foodVegetarian');
        $participantService->setAsEntered($cancelledTl);
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

        $event = $eventRepository->get(1);
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

        $participantService->setAsEntered($patrolParticipant);

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
}
