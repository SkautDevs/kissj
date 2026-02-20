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
}
