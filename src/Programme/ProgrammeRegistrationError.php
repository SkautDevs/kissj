<?php

declare(strict_types=1);

namespace kissj\Programme;

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
            self::NotPaid => 'flash.error.programmeNotPaid',
            self::Closed => 'flash.error.programmeClosed',
            self::NotFound => 'flash.error.programmeNotFound',
            self::NotPossible => 'flash.error.programmeNotPossible',
            self::WrongRole => 'flash.error.programmeWrongRole',
            self::AlreadyRegistered => 'flash.error.programmeAlreadyRegistered',
            self::NotRegistered => 'flash.error.programmeNotRegistered',
            self::Conflict => 'flash.error.programmeConflict',
            self::Full => 'flash.error.programmeFull',
            self::PreregisteredNoAssign => 'flash.warning.programmePreregisteredNoAssign',
            self::CancelledNoAssign => 'flash.warning.programmeCancelledNoAssign',
            self::AlreadyAssigned => 'flash.warning.programmeAlreadyAssigned',
            self::NotAssigned => 'flash.warning.programmeNotAssigned',
        };
    }
}
