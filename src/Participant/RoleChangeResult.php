<?php

declare(strict_types=1);

namespace kissj\Participant;

enum RoleChangeResult: string
{
    case Success = 'flash.success.roleChanged';
    case PatrolHasParticipants = 'flash.warning.patrolHasParticipantsCannotChangeRole';
    case TroopHasParticipants = 'flash.warning.troopHasParticipantsCannotChangeRole';
    case SameRole = 'flash.warning.sameRoleCannotChangeRole';
    case NotOpen = 'flash.warning.notOpenCannotChangeRole';
    case RoleNotValid = 'flash.error.roleNotValid';
}
