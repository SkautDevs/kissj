<?php

namespace kissj\Participant\Patrol;

use kissj\Orm\Repository;

/**
 * @table participant
 */
class PatrolLeaderRepository extends Repository {
    /**
     * @return PatrolLeader[]
     */
    public function findAll(): array {
        $patrolLeadersOnly = [];
        foreach (parent::findAll() as $participant) {
            if ($participant instanceof PatrolLeader) {
                $patrolLeadersOnly[] = $participant;
            }
        }

        return $patrolLeadersOnly;
    }
}
