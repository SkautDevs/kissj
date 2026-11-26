<?php

declare(strict_types=1);

namespace kissj\Device;

enum DevicePairingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Consumed = 'consumed';
    case Replaced = 'replaced';
}
