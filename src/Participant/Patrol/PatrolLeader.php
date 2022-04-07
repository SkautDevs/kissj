<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;

/**
 * @property PatrolParticipant[] $patrolParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class PatrolLeader extends Participant
{
}
