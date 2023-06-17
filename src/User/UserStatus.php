<?php

declare(strict_types=1);

namespace kissj\User;

enum UserStatus: string
{
    case WithoutRole = 'withoutRole';
    case Open = 'open';
    case Closed = 'closed';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    
    public function isPaidOrCancelled(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled], true);
    }
}
