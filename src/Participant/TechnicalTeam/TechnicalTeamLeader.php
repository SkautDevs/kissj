<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

/**
 * @property string|null                $technicalTeamName m:column(patrol_name)
 * @property TechnicalTeamParticipant[] $technicalTeamParticipants m:belongsToMany(patrol_leader_id:participant)
 */
class TechnicalTeamLeader extends Participant
{
    protected function initDefaults(): void
    {
        parent::initDefaults();
        $this->role = ParticipantRole::TechnicalTeamLeader;
    }

    public function getTechnicalTeamParticipantsCount(): int
    {
        return count($this->technicalTeamParticipants);
    }
}
