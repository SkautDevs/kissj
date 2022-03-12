<?php declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method TroopLeader get(int $istId)
 * @method TroopLeader[] findBy(mixed[] $criteria)
 * @method TroopLeader|null findOneBy(mixed[] $criteria)
 * @method TroopLeader getOneBy(mixed[] $criteria)
 */
class TroopLeaderRepository extends Repository
{
    /**
     * @param Event $event
     * @return TroopLeader[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $troopLeaders = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof TroopLeader && $participant->getUserButNotNull()->event->id === $event->id) {
                $troopLeaders[] = $participant;
            }
        }

        return $troopLeaders;
    }
}
