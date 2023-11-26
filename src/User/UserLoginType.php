<?php

declare(strict_types=1);

namespace kissj\User;

enum UserLoginType: string
{
    case Email = 'email';
    case Skautis = 'skautis';
}
