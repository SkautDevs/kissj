<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ParticipantDashboardFullFlashTest extends AppTestCase
{
    public function testDashboardFlashesFullRegistrationOnlyWhenEventIsFull(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $participantService = $this->getService($app, ParticipantService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $countBefore = $participantService->getParticipantsComingToEventCount($event);

        $this->createClosedPatrol($app, $event, 'full-flash');

        // leader + 2 members: if patrol members were not counted, the coming count would
        // only rise by 1 (the leader), landing below this cap and the flash would not show
        $event->maximalClosedParticipantsCount = $countBefore + 3;
        $eventRepository->persist($event);

        self::assertStringContainsString(
            'Už máme plno',
            $this->fetchDashboardAsFreshOpenIst($app, $event, 'full-flash-below-cap'),
        );

        $event = $eventRepository->get($event->id);
        $event->maximalClosedParticipantsCount = $countBefore + 4;
        $eventRepository->persist($event);

        self::assertStringNotContainsString(
            'Už máme plno',
            $this->fetchDashboardAsFreshOpenIst($app, $event, 'full-flash-above-cap'),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createClosedPatrol(App $app, Event $event, string $emailPrefix): void
    {
        $userService = $this->getService($app, UserService::class);
        $leaderUser = $userService->registerEmailUser($emailPrefix . '-leader@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');

        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        for ($i = 1; $i <= 2; $i++) {
            $member = new PatrolParticipant();
            $member->patrolLeader = $patrolLeader;
            $member->email = $emailPrefix . '-member-' . $i . '@example.com';
            $participantRepository->persist($member);
        }

        $leaderUser->status = UserStatus::Closed;
        $this->getService($app, UserRepository::class)->persist($leaderUser);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function fetchDashboardAsFreshOpenIst(App $app, Event $event, string $emailPrefix): string
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($emailPrefix . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }
}
