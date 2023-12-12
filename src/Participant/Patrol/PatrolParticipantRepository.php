<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method PatrolParticipant get(int $patrolParticipantId)
 * @method PatrolParticipant getOneBy(mixed[] $criteria)
 * @method PatrolParticipant[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method PatrolParticipant|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class PatrolParticipantRepository extends Repository
{
    /**
     * @return PatrolParticipant[]
     */
    public function findAllPatrolParticipantsForPatrolLeader(PatrolLeader $patrolLeader): array
    {
        return $this->findBy(['patrol_leader_id' => $patrolLeader->id]);
    }
}
