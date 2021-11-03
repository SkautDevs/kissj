<?php

declare(strict_types=1);

namespace kissj\Event\EventType;

use kissj\Event\ContentArbiterIst;
use kissj\Event\ContentArbiterPatrolLeader;
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

    public function getContentArbiterIst(): ContentArbiterIst
    {
        $ca = parent::getContentArbiterIst();
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->contingent = true;

        $ca->skills = true;

        return $ca;
    }

    public function getContentArbiterPatrolLeader(): ContentArbiterPatrolLeader
    {
        $ca = parent::getContentArbiterPatrolLeader();
        $ca->country = true;
        $ca->idNumber = true;
        $ca->languages = true;
        $ca->food = true;
        $ca->phone = true;
        $ca->email = true;
        $ca->swimming = true;
        $ca->contingent = true;

        return $ca;
    }
}
