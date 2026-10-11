<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Participant\ParticipantRole;

class EventService
{
    // null means "no limit"
    public function getDbMaxForRole(Event $event, ParticipantRole $role): ?int
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $event->maximalClosedPatrolsCount,
            ParticipantRole::TroopLeader => $event->maximalClosedTroopLeadersCount,
            ParticipantRole::TroopParticipant => $event->maximalClosedTroopParticipantsCount,
            ParticipantRole::Ist => $event->maximalClosedIstsCount,
            ParticipantRole::Guest => $event->maximalClosedGuestsCount,
            ParticipantRole::OrganizingTeam => $event->maximalClosedOrganizingTeamCount,
            ParticipantRole::PatrolParticipant => $event->maximalPatrolParticipantsCount,
            ParticipantRole::TechnicalTeamLeader, ParticipantRole::TechnicalTeamParticipant => null,
        };
    }
}
