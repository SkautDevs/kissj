<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LogicException;
use Slim\App;
use Tests\AppTestCase;

class CancelPatrolParticipantTest extends AppTestCase
{
    /**
     * Cancelling a single patrol participant used to cancel the patrol leader's
     * shared user, which made the whole patrol disappear from the entry app list.
     */
    public function testAdminCancelPatrolParticipantDoesNotCancelWholePatrol(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $userRepository = $this->getService($app, UserRepository::class);

        [$patrolLeader, $patrolParticipant] = $this->makePatrol($app);
        $event = $this->getSmallTestEvent($eventRepository);
        $leaderUser = $patrolLeader->getUserButNotNull();

        $adminUser = $this->createAdminUser($app);
        $adminUser->event = $event;
        $userRepository->persist($adminUser);
        $_SESSION['user'] = ['id' => $adminUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/changeRole/' . $patrolParticipant->id . '/cancel',
            'POST',
        ));

        self::assertLessThan(500, $response->getStatusCode());

        $leaderUserAfter = $userRepository->get($leaderUser->id);
        self::assertSame(
            UserStatus::Paid,
            $leaderUserAfter->status,
            'Cancelling one patrol participant must not cancel the patrol leader\'s user (whole patrol would vanish from the entry app)',
        );
    }

    public function testServiceRefusesCancellingPatrolParticipant(): void
    {
        $app = $this->getTestApp();
        $participantService = $this->getService($app, ParticipantService::class);
        [, $patrolParticipant] = $this->makePatrol($app);

        $this->expectException(LogicException::class);
        $participantService->cancelParticipant($patrolParticipant);
    }

    public function testServiceRefusesCancellingPatrolLeader(): void
    {
        $app = $this->getTestApp();
        $participantService = $this->getService($app, ParticipantService::class);
        [$patrolLeader] = $this->makePatrol($app);

        $this->expectException(LogicException::class);
        $participantService->cancelParticipant($patrolLeader);
    }

    /**
     * @param App<\Psr\Container\ContainerInterface> $app
     * @return array{0: PatrolLeader, 1: PatrolParticipant}
     */
    private function makePatrol(App $app): array
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $event = $this->getSmallTestEvent($eventRepository);

        $leaderEmail = 'cancel-pp-leader-' . bin2hex(random_bytes(4)) . '@example.com';
        $leaderUser = $userService->registerEmailUser($leaderEmail, $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Cancel';
        $patrolLeaderRepository->persist($patrolLeader);

        $leaderUser->status = UserStatus::Paid;
        $userRepository->persist($leaderUser);

        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->firstName = 'Member';
        $patrolParticipant->lastName = 'Cancelled';
        $patrolParticipantRepository->persist($patrolParticipant);

        return [$patrolLeader, $patrolParticipant];
    }
}
