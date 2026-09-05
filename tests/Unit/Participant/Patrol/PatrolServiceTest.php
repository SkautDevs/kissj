<?php

declare(strict_types=1);

namespace Tests\Unit\Participant\Patrol;

use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class PatrolServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetPatrolLeaderEnsuresUniqueTieCodeBeforePersist(): void
    {
        $user = new User(['id' => 1]);

        // Mockery::capture() fills these by reference, which PHPStan cannot see
        /** @var PatrolLeader|null $ensured */
        $ensured = null;
        /** @var PatrolLeader|null $persisted */
        $persisted = null;

        $patrolLeaderRepository = Mockery::mock(PatrolLeaderRepository::class);
        $patrolLeaderRepository->shouldReceive('findOneBy')->with(['user' => $user])->andReturn(null);

        $participantRepository = Mockery::mock(ParticipantRepository::class);
        $participantRepository->shouldReceive('ensureUniqueTieCode')
            ->once()
            ->globally()
            ->ordered()
            ->with(Mockery::capture($ensured));

        $patrolLeaderRepository->shouldReceive('persist')
            ->once()
            ->globally()
            ->ordered()
            ->with(Mockery::capture($persisted));

        $patrolService = new PatrolService(
            $patrolLeaderRepository,
            Mockery::mock(PatrolParticipantRepository::class),
            $participantRepository,
        );

        $returned = $patrolService->getPatrolLeader($user);

        self::assertSame(ParticipantRole::PatrolLeader, $returned->role);
        self::assertSame($ensured, $persisted);
        self::assertSame($persisted, $returned);
    }
}
