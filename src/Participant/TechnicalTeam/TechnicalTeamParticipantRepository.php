<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\Orm\Order;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method TechnicalTeamParticipant get(int $technicalTeamParticipantId)
 * @method TechnicalTeamParticipant getOneBy(mixed[] $criteria)
 * @method TechnicalTeamParticipant[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method TechnicalTeamParticipant|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class TechnicalTeamParticipantRepository extends Repository
{
    /**
     * @return TechnicalTeamParticipant[]
     */
    public function findAllForTechnicalTeamLeader(TechnicalTeamLeader $technicalTeamLeader): array
    {
        return $this->findBy(['patrol_leader_id' => $technicalTeamLeader->id]);
    }
}
