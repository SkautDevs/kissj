<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;

/**
 * @property PatrolLeader $patrolLeader m:hasOne(patrol_leader_id)
 */
class PatrolParticipant extends Participant
{
    protected function initDefaults(): void
    {
        $this->role = 'pp';
    }
}
