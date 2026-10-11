<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;

class ParticipantRoleTest extends TestCase
{
    public function testOrganizingTeamRoleExists(): void
    {
        $role = ParticipantRole::from('ot');
        self::assertSame(ParticipantRole::OrganizingTeam, $role);
    }

    public function testAllIncludesOrganizingTeam(): void
    {
        self::assertContains(ParticipantRole::OrganizingTeam, ParticipantRole::all());
    }

    public function testTechnicalTeamRolesExist(): void
    {
        self::assertSame(ParticipantRole::TechnicalTeamLeader, ParticipantRole::from('ttl'));
        self::assertSame(ParticipantRole::TechnicalTeamParticipant, ParticipantRole::from('ttp'));
        self::assertContains(ParticipantRole::TechnicalTeamLeader, ParticipantRole::all());
        self::assertContains(ParticipantRole::TechnicalTeamParticipant, ParticipantRole::all());
    }

    public function testOnlyLeaderOwnedRolesHaveNoOwnUser(): void
    {
        self::assertFalse(ParticipantRole::PatrolParticipant->hasOwnUser());
        self::assertFalse(ParticipantRole::TechnicalTeamParticipant->hasOwnUser());
        self::assertTrue(ParticipantRole::TechnicalTeamLeader->hasOwnUser());
        self::assertTrue(ParticipantRole::TroopParticipant->hasOwnUser());
        self::assertTrue(ParticipantRole::PatrolLeader->hasOwnUser());
    }
}
