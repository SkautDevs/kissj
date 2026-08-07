<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class PatrolLeaderTest extends AppTestCase
{
    public function testCreatePatrolLeader(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $testEvent = $eventRepository->get(1);

        $patrolService = $this->getService($app, PatrolService::class);

        $email = 'test@example.com';
        $user = $userService->registerEmailUser($email, $testEvent);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        self::assertEquals($user->id, $patrolLeader->getUserButNotNull()->id);
        self::assertEquals(ParticipantRole::PatrolLeader, $patrolLeader->role);
        self::assertEquals(UserRole::Participant, $patrolLeader->getUserButNotNull()->role);
        self::assertEquals(UserStatus::WithoutRole, $patrolLeader->getUserButNotNull()->status);
    }

    public function testDashboardOmitsMaximumWhenPatrolMaximumIsNull(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $this->mutateEventForTest($app->getContainer(), $eventRepository->get(1), [
            'minimalPatrolParticipantsCount' => 2,
            'maximalPatrolParticipantsCount' => null,
        ]);

        $body = $this->fetchDashboardForOpenPatrolLeader($app, 'pl-dashboard-null-max@example.com');

        // NULL maximum means no upper bound, so the "and maximally <blank>" clause must be gone
        self::assertStringContainsString('nejméně 2', $body);
        self::assertStringNotContainsString('a nejvíce', $body);
    }

    public function testDashboardOmitsHelpTextWhenPatrolSizeIsUnconstrained(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $this->mutateEventForTest($app->getContainer(), $eventRepository->get(1), [
            'minimalPatrolParticipantsCount' => 0,
            'maximalPatrolParticipantsCount' => null,
        ]);

        $body = $this->fetchDashboardForOpenPatrolLeader($app, 'pl-dashboard-no-limits@example.com');

        self::assertStringNotContainsString('Potřebuješ', $body);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function fetchDashboardForOpenPatrolLeader(App $app, string $email): string
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);

        $user = $userService->registerEmailUser($email, $event);
        // setting the role also moves the user to Open, which the help-text block requires
        $userService->createParticipantSetRole($user, 'pl');

        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }
}
