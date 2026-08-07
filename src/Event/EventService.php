<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Participant\ParticipantRole;

class EventService
{
    // deliberately not delegating to EventType::getMaximalCountForRole - this is the raw DB
    // value, and showRoleManagement compares the two to detect an event-type override
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
        };
    }
}
