<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

class EventTypeCej extends EventType
{
    public function getMaximumClosedParticipants(Participant $participant): int
    {
        if ($participant instanceof PatrolLeader) {
            return match ($participant->country) {
                'Slovak' => 15,
                'Czech' => 10,
                default => 20,
            };
        }

        return parent::getMaximumClosedParticipants($participant);
    }
}
