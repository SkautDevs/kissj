<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;

/**
 * @property int                 $id
 * @property string|null         $patrolName
 * @property PatrolParticipant[] $patrolParticipants m:belongsToMany
 * @property Participant         $participant        m:belongsToOne
 */
class PatrolLeader extends Participant {

}
