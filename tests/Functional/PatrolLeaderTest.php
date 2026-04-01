<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class PatrolLeaderTest extends AppTestCase
{
    public function testCreatePatrolLeader(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $testEvent = $eventRepository->get(1);

        /** @var PatrolService $patrolService */
        $patrolService = $container->get(PatrolService::class);

        $email = 'test@example.com';
        $user = $userService->registerEmailUser($email, $testEvent);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        self::assertEquals($user->id, $patrolLeader->getUserButNotNull()->id);
        self::assertEquals(ParticipantRole::PatrolLeader, $patrolLeader->role);
        self::assertEquals(UserRole::Participant, $patrolLeader->getUserButNotNull()->role);
        self::assertEquals(UserStatus::WithoutRole, $patrolLeader->getUserButNotNull()->status);
    }
}
