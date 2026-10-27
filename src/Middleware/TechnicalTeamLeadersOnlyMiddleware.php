<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Participant\ParticipantRole;

class TechnicalTeamLeadersOnlyMiddleware extends AbstractRoleOnlyMiddleware
{
    protected function getRole(): ParticipantRole
    {
        return ParticipantRole::TechnicalTeamLeader;
    }

    protected function getFlashKey(): string
    {
        return 'flash.error.ttlOnly';
    }
}
