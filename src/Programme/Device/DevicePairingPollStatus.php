<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

enum DevicePairingPollStatus: string
{
    case Pending = 'pending';
    case SlowDown = 'slowDown';
    case Approved = 'approved';
    case Denied = 'denied';
    case Expired = 'expired';
}
