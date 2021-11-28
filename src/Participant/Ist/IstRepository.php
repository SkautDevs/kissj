<?php

namespace kissj\Participant\Ist;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 */
class IstRepository extends Repository
{
    /**
     * @param Event $event
     * @return Ist[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $ists = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof Ist && $participant->user->event->id === $event->id) {
                $ists[] = $participant;
            }
        }

        return $ists;
    }
}
