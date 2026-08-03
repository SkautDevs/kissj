<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class PatrolShowParticipantOwnershipTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testPatrolLeaderCanShowOwnParticipant(): void
    {
        $app = $this->getTestApp();
        [, $leaderUser, $participant] = $this->createPatrolLeaderWithParticipant(
            $app->getContainer(),
            'owner-leader-' . bin2hex(random_bytes(4)) . '@example.com',
            'Alice',
            'Ownersson',
        );

        $_SESSION['user'] = ['id' => $leaderUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/patrol/participant/' . $participant->id . '/show'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Ownersson', (string)$response->getBody());
    }

    public function testForeignPatrolLeaderCannotShowOthersParticipant(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();

        [, , $participant] = $this->createPatrolLeaderWithParticipant(
            $container,
            'owner-leader-' . bin2hex(random_bytes(4)) . '@example.com',
            'Bob',
            'Secretsson',
        );
        [, $foreignLeaderUser] = $this->createPatrolLeaderWithParticipant(
            $container,
            'foreign-leader-' . bin2hex(random_bytes(4)) . '@example.com',
            'Eve',
            'Otherperson',
        );

        $_SESSION['user'] = ['id' => $foreignLeaderUser->id];
        $app = $this->getTestApp(false);

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/patrol/participant/' . $participant->id . '/show'),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertStringNotContainsString('Secretsson', (string)$response->getBody());
    }

    /**
     * @return array{0: PatrolLeader, 1: User, 2: PatrolParticipant}
     */
    private function createPatrolLeaderWithParticipant(
        ContainerInterface $container,
        string $email,
        string $firstName,
        string $lastName,
    ): array {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);

        $user = $userService->registerEmailUser($email, $event);
        $leaderParticipant = $userService->createParticipantSetRole($user, 'pl');

        /** @var PatrolLeaderRepository $patrolLeaderRepository */
        $patrolLeaderRepository = $container->get(PatrolLeaderRepository::class);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);

        /** @var PatrolParticipantRepository $patrolParticipantRepository */
        $patrolParticipantRepository = $container->get(PatrolParticipantRepository::class);

        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $patrolLeader;
        $patrolParticipant->firstName = $firstName;
        $patrolParticipant->lastName = $lastName;
        $patrolParticipantRepository->persist($patrolParticipant);

        return [$patrolLeader, $user, $patrolParticipant];
    }
}
