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

    /**
     * @return list<ParticipantRole>
     */
    public static function all(): array
    {
        return [
            self::Ist,
            self::PatrolLeader,
            self::PatrolParticipant,
            self::Guest,
            self::TroopLeader,
            self::TroopParticipant,
        ];
    }
}
