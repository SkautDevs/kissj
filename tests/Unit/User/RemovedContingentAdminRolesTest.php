<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use kissj\User\UserRole;
use PHPUnit\Framework\TestCase;

class RemovedContingentAdminRolesTest extends TestCase
{
    public function testRemovedRolesAreGone(): void
    {
        self::assertNull(UserRole::tryFrom('contingentAdminGb'));
        self::assertNull(UserRole::tryFrom('contingentAdminSw'));
    }
}
