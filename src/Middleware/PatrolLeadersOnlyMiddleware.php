<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\Participant\ParticipantRole;

class PatrolLeadersOnlyMiddleware extends AbstractRoleOnlyMiddleware
{
    protected function getRole(): ParticipantRole
    {
        return ParticipantRole::PatrolLeader;
    }

    protected function getFlashKey(): string
    {
        return 'flash.error.plOnly';
    }
}
