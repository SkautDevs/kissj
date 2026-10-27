<?php

declare(strict_types=1);

namespace Tests\Functional;

use DI\Container;
use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\Country;
use kissj\Participant\Gender;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Skautis\SkautisMemberData;
use kissj\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class PatrolAddParticipantTest extends AppTestCase
{
    use MockeryPHPUnitIntegration;

    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testShowAddParticipantDoesNotPersist(): void
    {
        $app = $this->getTestApp();
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader($app->getContainer(), 'pl-show-test@example.com');
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $_SESSION['user'] = ['id' => $leaderUser->id];
        $app = $this->getTestApp(false);

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
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader($app->getContainer(), 'pl-post-test@example.com');
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $_SESSION['user'] = ['id' => $leaderUser->id];
        $app = $this->getTestApp(false);

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

    public function testAddParticipantIgnoresArbiterDisallowedFields(): void
    {
        $app = $this->getTestApp();
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader(
            $app->getContainer(),
            'pl-disallowed-test@example.com',
        );
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $_SESSION['user'] = ['id' => $leaderUser->id];
        $app = $this->getTestApp(false);

        $initialCount = count(
            $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader),
        );

        $response = $app->handle(
            $this->createRequest(
                self::BASE_URL . '/patrol/addParticipant',
                'POST',
                [
                    'firstName' => 'Alice',
                    'lastName' => 'Anderson',
                    'contingent' => 'InjectedContingent',
                    'scarf' => 'yes',
                ],
            ),
        );

        self::assertSame(302, $response->getStatusCode());

        $afterParticipants = $patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader);
        self::assertCount($initialCount + 1, $afterParticipants);

        $newParticipant = end($afterParticipants);
        self::assertSame('Alice', $newParticipant->firstName);
        self::assertNull($newParticipant->contingent);
        self::assertNull($newParticipant->scarf);
    }

    public function testAddParticipantPersistsParticipantBeforePatrolParticipant(): void
    {
        $app = $this->getTestApp();
        [$patrolLeader, $leaderUser] = $this->createPatrolLeader($app->getContainer(), 'pl-tie-code-test@example.com');

        $_SESSION['user'] = ['id' => $leaderUser->id];
        $app = $this->getTestApp(false);

        $container = $app->getContainer();
        assert($container instanceof Container);

        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);
        $initialCount = count($patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader));

        // globally() makes ordered() span both mocks - plain ordered() only orders one mock's own calls
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantRepositoryMock = Mockery::mock($participantRepository)->makePartial();
        // the row is really INSERTed here, inside ParticipantService::addParamsIntoParticipant() - passthru()
        // would run the method on the uninitialised mock, so delegate to the proxied repository instead
        $participantRepositoryMock->shouldReceive('persist')
            ->once()
            ->globally()
            ->ordered()
            ->andReturnUsing(static fn (Participant $participant) => $participantRepository->persist($participant));

        $patrolParticipantRepositoryMock = Mockery::mock($patrolParticipantRepository)->makePartial();
        $patrolParticipantRepositoryMock->shouldReceive('persist')
            ->once()
            ->globally()
            ->ordered()
            ->andReturnUsing(
                static fn (PatrolParticipant $participant) => $patrolParticipantRepository->persist($participant),
            );

        $container->set(ParticipantRepository::class, $participantRepositoryMock);
        $container->set(PatrolParticipantRepository::class, $patrolParticipantRepositoryMock);

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
        self::assertMatchesRegularExpression(self::TIE_CODE_PATTERN, end($afterParticipants)->tieCode);
    }

    // a unit test cannot cover the factory: LeanMapper refuses a detached PatrolLeader on the relationship
    public function testCreatePatrolParticipantDoesNotPersist(): void
    {
        $app = $this->getTestApp();
        [$patrolLeader] = $this->createPatrolLeader($app->getContainer(), 'pl-factory-test@example.com');

        $patrolParticipant = $this->getService($app, PatrolService::class)->createPatrolParticipant($patrolLeader);

        self::assertTrue($patrolParticipant->isDetached());
        self::assertSame($patrolLeader->id, $patrolParticipant->patrolLeader->id);
        self::assertMatchesRegularExpression(self::TIE_CODE_PATTERN, $patrolParticipant->tieCode);
    }

    public function testAddPatrolParticipantFromSkautisPersistsOnce(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        assert($container instanceof Container);

        // no HTTP request here, so the service is driven on the same container that created the leader
        $patrolLeader = $this->createPatrolLeader($container, 'pl-skautis-tie-code-test@example.com')[0];

        // Mockery::capture() fills this by reference, which PHPStan cannot see
        /** @var PatrolParticipant|null $persisted */
        $persisted = null;
        $patrolParticipantRepositoryMock = Mockery::mock($this->getService($app, PatrolParticipantRepository::class))
            ->makePartial();
        $patrolParticipantRepositoryMock->shouldReceive('persist')
            ->once()
            ->with(Mockery::capture($persisted));

        // PHP-DI caches autowired instances, so swap the repositories before PatrolService is first resolved
        $container->set(PatrolParticipantRepository::class, $patrolParticipantRepositoryMock);

        $patrolService = $this->getService($app, PatrolService::class);

        $memberData = new SkautisMemberData(
            1,
            'Eva',
            'Dvořáková',
            '',
            DateTimeUtils::getDateTime('2005-03-10'),
            '',
            '',
            '',
            Country::Other,
            Gender::Woman,
        );

        $returned = $patrolService->addPatrolParticipantFromSkautis($patrolLeader, $memberData);

        self::assertSame($persisted, $returned);
        // the getter rebuilds the PatrolLeader from the referenced Row, so compare by id
        self::assertSame($patrolLeader->id, $returned->patrolLeader->id);
    }

    /**
     * @return array{0: PatrolLeader, 1: User}
     */
    private function createPatrolLeader(ContainerInterface $container, string $email): array
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);

        $participant = $this->createParticipantWithRole($container, 'pl', $event, $email);

        /** @var PatrolLeaderRepository $patrolLeaderRepository */
        $patrolLeaderRepository = $container->get(PatrolLeaderRepository::class);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($participant->id);

        return [$patrolLeader, $participant->getUserButNotNull()];
    }
}
