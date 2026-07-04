<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use kissj\User\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleProgrammeTest extends TestCase
{
    public function testProgrammeAdminIsNotABlanketAdminRole(): void
    {
        self::assertNotContains(UserRole::ProgrammeAdmin, UserRole::adminRoles());
    }

    public function testManageProgrammesEligibility(): void
    {
        self::assertTrue(UserRole::Admin->isEligibleToManageProgrammes());
        self::assertTrue(UserRole::ProgrammeAdmin->isEligibleToManageProgrammes());
        self::assertFalse(UserRole::IstAdmin->isEligibleToManageProgrammes());
        self::assertFalse(UserRole::Participant->isEligibleToManageProgrammes());
    }

    public function testProgrammeAdminCannotHandlePaymentsOrTroops(): void
    {
        self::assertFalse(UserRole::ProgrammeAdmin->isEligibleToHandlePayments());
        self::assertFalse(UserRole::ProgrammeAdmin->isEligibleToManageTroops());
    }
}
