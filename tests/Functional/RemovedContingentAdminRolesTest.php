<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\User\UserRole;
use Tests\AppTestCase;

class RemovedContingentAdminRolesTest extends AppTestCase
{
    public function testRemovedRolesAreGone(): void
    {
        self::assertNull(UserRole::tryFrom('contingentAdminGb'));
        self::assertNull(UserRole::tryFrom('contingentAdminSw'));
    }
}
