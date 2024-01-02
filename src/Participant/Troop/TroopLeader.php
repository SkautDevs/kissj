<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Participant\Participant;

/**
 * @property TroopParticipant[] $troopParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class TroopLeader extends Participant
{
    public function getTroopParticipantsCount(): int
    {
        // TODO optimalize to not getting entities and count rows instead + use in app
        return count($this->troopParticipants);
    }
}
