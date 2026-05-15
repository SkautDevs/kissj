<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class PatrolAddParticipantTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testShowAddParticipantDoesNotPersist(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainerOrFail($app);
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader($container, 'pl-show-test@example.com');

        $_SESSION['user']['id'] = $leaderUser->id;
        $app = $this->getTestApp(false);

        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);
        $initialCount = count(
            $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader),
        );

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/patrol/addParticipant', 'GET'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(
            $initialCount,
            $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader),
        );
    }

    public function testAddParticipantPersistsSubmittedData(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainerOrFail($app);
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader($container, 'pl-post-test@example.com');

        $_SESSION['user']['id'] = $leaderUser->id;
        $app = $this->getTestApp(false);

        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);
        $initialCount = count(
            $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader),
        );

        $response = $app->handle(
            $this->createRequest(
                self::BASE_URL . '/patrol/addParticipant',
                'POST',
                ['firstName' => 'Alice', 'lastName' => 'Anderson'],
            ),
        );

        self::assertSame(302, $response->getStatusCode());

        $afterParticipants = $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader);
        self::assertCount($initialCount + 1, $afterParticipants);

        $newParticipant = end($afterParticipants);
        self::assertSame('Alice', $newParticipant->firstName);
        self::assertSame('Anderson', $newParticipant->lastName);
    }

    private function getContainerOrFail(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new \RuntimeException('Container is null');
        }
        return $container;
    }

    /**
     * @return array{0: PatrolLeader, 1: User}
     */
    private function createPatrolLeader(ContainerInterface $container, string $email): array
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);

        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'pl');

        /** @var PatrolLeaderRepository $patrolLeaderRepository */
        $patrolLeaderRepository = $container->get(PatrolLeaderRepository::class);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($participant->id);

        return [$patrolLeader, $user];
    }
}
