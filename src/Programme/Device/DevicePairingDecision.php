<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

enum DevicePairingDecision: string
{
    case Approve = 'approve';
    case Deny = 'deny';
}
