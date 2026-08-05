<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminDashboardComingCountTest extends AppTestCase
{
    public function testDashboardShowsComingCountWithoutCap(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event->maximalClosedParticipantsCount = null;
        $eventRepository->persist($event);

        $this->loginAdmin($app, $event);

        $countBefore = $this->parseComingCount($this->fetchDashboard($event));

        $this->createClosedPatrol($app, $event, 'dashboard-nocap', 2);

        // leader + 2 members; no cap set, so the line ends right after the count
        self::assertStringContainsString(
            'Celkem přijede na akci: ' . ($countBefore + 3) . '</p>',
            $this->fetchDashboard($event),
        );
    }

    public function testDashboardShowsComingCountWithCap(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $this->createClosedPatrol($app, $event, 'dashboard-cap', 1);

        $this->loginAdmin($app, $event);

        $count = $this->parseComingCount($this->fetchDashboard($event));

        $eventRepository = $this->getService($app, EventRepository::class);
        $event->maximalClosedParticipantsCount = $count + 5;
        $eventRepository->persist($event);

        self::assertStringContainsString(
            'Celkem přijede na akci: ' . $count . ' / ' . ($count + 5) . '</p>',
            $this->fetchDashboard($event),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function loginAdmin(App $app, Event $event): void
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $adminUser = $this->createAdminUser($app);
        $adminUser->status = UserStatus::Open;
        // createAdminUser() builds the admin against event 1, but LoggedOnlyMiddleware logs the
        // user out when their event does not match the event in the URL, so it must be repointed.
        $adminUser->event = $event;
        $userRepository->persist($adminUser);

        $_SESSION['user'] = ['id' => $adminUser->id];
    }

    private function fetchDashboard(Event $event): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    private function parseComingCount(string $body): int
    {
        self::assertSame(
            1,
            preg_match('#Celkem přijede na akci: (\d+)#u', $body, $matches),
            'coming-to-event line not found in dashboard body',
        );

        return (int)$matches[1];
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createClosedPatrol(App $app, Event $event, string $emailPrefix, int $membersCount): void
    {
        $userService = $this->getService($app, UserService::class);
        $leaderUser = $userService->registerEmailUser($emailPrefix . '-leader@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');

        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        for ($i = 1; $i <= $membersCount; $i++) {
            $member = new PatrolParticipant();
            $member->patrolLeader = $patrolLeader;
            $member->email = $emailPrefix . '-member-' . $i . '@example.com';
            $participantRepository->persist($member);
        }

        $leaderUser->status = UserStatus::Closed;
        $this->getService($app, UserRepository::class)->persist($leaderUser);
    }
}
