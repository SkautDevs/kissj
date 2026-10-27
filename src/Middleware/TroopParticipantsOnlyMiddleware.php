<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Participant\ParticipantRole;

class TroopParticipantsOnlyMiddleware extends AbstractRoleOnlyMiddleware
{
    protected function getRole(): ParticipantRole
    {
        return ParticipantRole::TroopParticipant;
    }

    protected function getFlashKey(): string
    {
        return 'flash.error.tpOnly';
    }
}
