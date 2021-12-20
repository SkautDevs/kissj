<?php declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\Event\Event;
use kissj\Orm\Repository;

/**
 * @table participant
 */
class GuestRepository extends Repository
{
    /**
     * @param Event $event
     * @return Guest[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $guests = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof Guest && $participant->getUserButNotNull()->event->id === $event->id) {
                $guests[] = $participant;
            }
        }

        return $guests;
    }
}
