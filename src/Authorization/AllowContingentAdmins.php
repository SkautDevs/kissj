<?php

declare(strict_types=1);

namespace kissj\Authorization;

use kissj\User\UserRole;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class AllowContingentAdmins implements AdminRouteAccess
{
    public function allows(UserRole $role): bool
    {
        return in_array($role, UserRole::contingentAdminRoles(), true);
    }
}
