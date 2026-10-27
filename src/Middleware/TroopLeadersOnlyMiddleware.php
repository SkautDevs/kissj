<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Participant\ParticipantRole;

class TroopLeadersOnlyMiddleware extends AbstractRoleOnlyMiddleware
{
    protected function getRole(): ParticipantRole
    {
        return ParticipantRole::TroopLeader;
    }

    protected function getFlashKey(): string
    {
        return 'flash.error.tlOnly';
    }
}
