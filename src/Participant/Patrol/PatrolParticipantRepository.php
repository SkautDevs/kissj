<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method PatrolParticipant[] findBy(mixed[] $criteria)
 * @method PatrolParticipant|null findOneBy(mixed[] $criteria)
 * @method PatrolParticipant getOneBy(mixed[] $criteria)
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
