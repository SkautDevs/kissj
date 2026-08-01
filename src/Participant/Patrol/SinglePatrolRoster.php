<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

readonly class SinglePatrolRoster
{
    /**
     * @param array<array{name: string, tshirtSize: string|null}> $patrolParticipants
     */
    public function __construct(
        public string $patrolId,
        public string $patrolName,
        public string $contingent,
        public string $patrolLeaderName,
        public ?string $patrolLeaderTshirtSize,
        public array $patrolParticipants,
    ) {
    }
}
