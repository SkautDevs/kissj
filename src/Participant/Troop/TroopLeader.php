<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Participant\Participant;
use kissj\User\UserStatus;

/**
 * @property string|null        $patrolName
 * @property TroopParticipant[] $troopParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class TroopLeader extends Participant
{
    public function getTroopParticipantsCount(): int
    {
        // TODO optimize to not getting entities and count rows instead + use in app
        return count($this->troopParticipants);
    }

    /**
     * @return list<TroopParticipant>
     */
    public function getNotApprovedTroopParticipants(): array
    {
        return array_values(array_filter(
            $this->troopParticipants,
            static fn (TroopParticipant $troopParticipant): bool => in_array(
                $troopParticipant->getUserButNotNull()->status,
                [UserStatus::Open, UserStatus::Closed],
                true,
            ),
        ));
    }
}
