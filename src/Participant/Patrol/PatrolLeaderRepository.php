<?php

namespace kissj\Participant\Patrol;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 */
class PatrolLeaderRepository extends Repository
{
    /**
     * @param Event $event
     * @return PatrolLeader[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $patrolLeaders = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof PatrolLeader && $participant->user->event->id === $event->id) {
                $patrolLeaders[] = $participant;
            }
        }

        return $patrolLeaders;
    }
}
