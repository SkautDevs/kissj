<?php

declare(strict_types=1);

namespace kissj\Authorization;

use kissj\User\UserRole;

interface AdminRouteAccess
{
    public function allows(UserRole $role): bool;
}
