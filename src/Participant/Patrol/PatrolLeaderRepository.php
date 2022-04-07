<?php

declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method PatrolLeader get(int $patrolLeaderId)
 * @method PatrolLeader[] findBy(mixed[] $criteria)
 * @method PatrolLeader|null findOneBy(mixed[] $criteria)
 * @method PatrolLeader getOneBy(mixed[] $criteria)
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
            if ($participant instanceof PatrolLeader && $participant->getUserButNotNull()->event->id === $event->id) {
                $patrolLeaders[] = $participant;
            }
        }

        return $patrolLeaders;
    }
}
