<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

readonly class SinglePatrolRoster
{
    /**
     * @param array<string> $patrolParticipantNames
     */
    public function __construct(
        public string $patrolId,
        public string $patrolName,
        public string $contingent,
        public string $patrolLeaderName,
        public array $patrolParticipantNames,
    ) {
    }
}
