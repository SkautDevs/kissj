<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

readonly class PatrolsRoster
{
    /**
     * @param array<SinglePatrolRoster> $patrolsRoster
     */
    public function __construct(
        public array $patrolsRoster,
    ) {
    }
}
