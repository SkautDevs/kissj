<?php

declare(strict_types=1);

namespace kissj\Participant;

enum ParticipantRole: string
{
    case Ist = 'ist';
    case PatrolLeader = 'pl';
    case PatrolParticipant = 'pp';
    case Guest = 'guest';
    case TroopLeader = 'tl';
    case TroopParticipant = 'tp';
    case OrganizingTeam = 'ot';
    case TechnicalTeamLeader = 'ttl';
    case TechnicalTeamParticipant = 'ttp';

    /**
     * @return list<ParticipantRole>
     */
    public static function all(): array
    {
        return [
            self::PatrolLeader,
            self::PatrolParticipant,
            self::TroopLeader,
            self::TroopParticipant,
            self::Ist,
            self::Guest,
            self::OrganizingTeam,
            self::TechnicalTeamLeader,
            self::TechnicalTeamParticipant,
        ];
    }

    public function hasOwnUser(): bool
    {
        return match ($this) {
            self::PatrolParticipant, self::TechnicalTeamParticipant => false,
            self::PatrolLeader,
            self::TroopLeader,
            self::TroopParticipant,
            self::Ist,
            self::Guest,
            self::OrganizingTeam,
            self::TechnicalTeamLeader => true,
        };
    }
}
