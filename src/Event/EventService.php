<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Participant\ParticipantRole;

class EventService
{
    public function getDbMaxForRole(Event $event, ParticipantRole $role): int
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $event->maximalClosedPatrolsCount ?? 0,
            ParticipantRole::TroopLeader => $event->maximalClosedTroopLeadersCount ?? 0,
            ParticipantRole::TroopParticipant => $event->maximalClosedTroopParticipantsCount ?? 0,
            ParticipantRole::Ist => $event->maximalClosedIstsCount ?? 0,
            ParticipantRole::Guest => $event->maximalClosedGuestsCount ?? 0,
            ParticipantRole::OrganizingTeam => $event->maximalClosedOrganizingTeamCount ?? 0,
            ParticipantRole::PatrolParticipant => $event->maximalPatrolParticipantsCount ?? 0,
        };
    }
}
