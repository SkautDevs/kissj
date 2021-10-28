<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;

abstract class EventType
{
    public function getPrice(Participant $participant): int
    {
        $user = $participant->user;
        if ($user === null) {
            throw new \RuntimeException('User in participant is missing');
        }

        return $user->event->defaultPrice;
    }

    public function getMaximumClosedParticipants(Participant $participant): int
    {
        return match (get_class($participant)) {
            PatrolLeader::class => $participant->user->event->maximalClosedPatrolsCount,
            Ist::class => $participant->user->event->maximalClosedIstsCount,
            Guest::class => $participant->user->event->maximalClosedGuestsCount,
            default => throw new \RuntimeException('Unexpected participent class: ' . get_class($participant)),
        };
    }
}
