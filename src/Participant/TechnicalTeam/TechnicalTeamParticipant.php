<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

/**
 * @property TechnicalTeamLeader $technicalTeamLeader m:hasOne(patrol_leader_id)
 */
class TechnicalTeamParticipant extends Participant
{
    protected function initDefaults(): void
    {
        parent::initDefaults();
        $this->role = ParticipantRole::TechnicalTeamParticipant;
    }
}
