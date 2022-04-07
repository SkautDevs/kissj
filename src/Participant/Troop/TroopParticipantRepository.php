<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 *
 * @method TroopParticipant get(int $istId)
 * @method TroopParticipant[] findBy(mixed[] $criteria)
 * @method TroopParticipant|null findOneBy(mixed[] $criteria)
 * @method TroopParticipant getOneBy(mixed[] $criteria)
 */
class TroopParticipantRepository extends Repository
{
    /**
     * @param Event $event
     * @return TroopParticipant[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $troopParticipants = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof TroopParticipant && $participant->getUserButNotNull()->event->id === $event->id) {
                $troopParticipants[] = $participant;
            }
        }

        return $troopParticipants;
    }
}
