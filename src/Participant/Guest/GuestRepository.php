<?php

namespace kissj\Participant\Guest;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 */
class GuestRepository extends Repository
{
    /**
     * @return Guest[]
     */

    /**
     * @param Event $event
     * @return Guest[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $guests = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof Guest && $participant->user->event->id === $event->id) {
                $guests[] = $participant;
            }
        }

        return $guests;
    }
}
