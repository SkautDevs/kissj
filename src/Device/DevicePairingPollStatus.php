<?php

declare(strict_types=1);

namespace kissj\Device;

enum DevicePairingPollStatus: string
{
    case Pending = 'pending';
    case SlowDown = 'slowDown';
    case Approved = 'approved';
    case Expired = 'expired';
}
