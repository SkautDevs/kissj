<?php

declare(strict_types=1);

namespace kissj\Programme;

use LogicException;

enum ProgrammeRegistrationError: string
{
    case NotPaid = 'notPaid';
    case Closed = 'closed';
    case NotFound = 'notFound';
    case NotPossible = 'notPossible';
    case WrongRole = 'wrongRole';
    case AlreadyRegistered = 'alreadyRegistered';
    case NotRegistered = 'notRegistered';
    case Conflict = 'conflict';
    case Full = 'full';
    case PreregisteredNoAssign = 'preregisteredNoAssign';
    case CancelledNoAssign = 'cancelledNoAssign';
    case AlreadyAssigned = 'alreadyAssigned';
    case NotAssigned = 'notAssigned';

    public function flashKey(): string
    {
        return match ($this) {
            self::NotFound => 'flash.error.programmeNotFound',
            self::PreregisteredNoAssign => 'flash.warning.programmePreregisteredNoAssign',
            self::CancelledNoAssign => 'flash.warning.programmeCancelledNoAssign',
            self::AlreadyAssigned => 'flash.warning.programmeAlreadyAssigned',
            self::NotAssigned => 'flash.warning.programmeNotAssigned',
            self::NotPaid,
            self::Closed,
            self::NotPossible,
            self::WrongRole,
            self::AlreadyRegistered,
            self::NotRegistered,
            self::Conflict,
            self::Full => throw new LogicException('Error ' . $this->value . ' has no flash text'),
        };
    }
}
