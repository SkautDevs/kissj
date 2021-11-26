<?php

namespace kissj\Participant\Patrol;

use kissj\Orm\Repository;

/**
 * @table participant
 */
class PatrolParticipantRepository extends Repository
{
    /**
     * @param PatrolLeader $patrolLeader
     * @return PatrolParticipant[]
     */
    public function findAllPatrolParticipantsForPatrolLeader(PatrolLeader $patrolLeader): array
    {
        return $this->findBy(['patrol_leader_id' => $patrolLeader->id]);
    }
}
