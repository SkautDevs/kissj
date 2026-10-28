<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

enum DevicePairingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Consumed = 'consumed';
    case Replaced = 'replaced';
}
