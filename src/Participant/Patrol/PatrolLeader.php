<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;

/**
 * @property PatrolParticipant[] $patrolParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class PatrolLeader extends Participant
{
    public function getPatrolParticipantsCount(): int
    {
        // TODO optimalize to not getting entities and count rows instead + use in app
        return count($this->patrolParticipants);
    }
}
