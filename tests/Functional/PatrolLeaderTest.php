<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserService;
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
        $user = $userService->registerUser($email, $testEvent);
        $patrolLeader = $patrolService->getPatrolLeader($user);

        $this->assertEquals($patrolLeader->getUserButNotNull()->id, $user->id);
        // $this->assertEquals('pl', $patrolLeader->role); // TODO need to fix
        // $this->assertEquals(User::ROLE_PATROL_LEADER, $patrolLeader->getUserButNotNull()->role); // TODO need to fix
        // this->assertEquals(User::STATUS_OPEN, $patrolLeader->getUserButNotNull()->status); // TODO need to fix
    }
}
