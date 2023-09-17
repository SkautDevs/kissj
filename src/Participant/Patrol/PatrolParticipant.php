<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

/**
 * @property PatrolLeader $patrolLeader m:hasOne(patrol_leader_id)
 */
class PatrolParticipant extends Participant
{
    protected function initDefaults(): void
    {
        parent::initDefaults();
        $this->role = ParticipantRole::PatrolParticipant;
    }
}
