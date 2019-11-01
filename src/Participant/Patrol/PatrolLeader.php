<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;

/**
 * @table participant
 *
 * @property string|null         $patrolName
 * @property PatrolParticipant[] $patrolParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class PatrolLeader extends Participant {

}
