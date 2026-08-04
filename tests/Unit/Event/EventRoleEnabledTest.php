<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Event\Event;
use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;

class EventRoleEnabledTest extends TestCase
{
    public function testPatrolRolesFollowAllowPatrols(): void
    {
        $event = new Event();
        $event->allowPatrols = true;
        $event->allowTroops = false;
        $event->allowIsts = false;
        $event->allowGuests = false;
        $event->allowOrganizingTeam = false;

        self::assertTrue($event->isRoleEnabled(ParticipantRole::PatrolLeader));
        self::assertTrue($event->isRoleEnabled(ParticipantRole::PatrolParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Ist));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Guest));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::OrganizingTeam));
    }

    public function testTroopRolesFollowAllowTroops(): void
    {
        $event = new Event();
        $event->allowPatrols = false;
        $event->allowTroops = true;
        $event->allowIsts = false;
        $event->allowGuests = false;
        $event->allowOrganizingTeam = false;

        self::assertTrue($event->isRoleEnabled(ParticipantRole::TroopLeader));
        self::assertTrue($event->isRoleEnabled(ParticipantRole::TroopParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolLeader));
    }

    public function testIstRoleFollowsAllowIsts(): void
    {
        $event = new Event();
        $event->allowPatrols = false;
        $event->allowTroops = false;
        $event->allowIsts = true;
        $event->allowGuests = false;
        $event->allowOrganizingTeam = false;

        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopParticipant));
        self::assertTrue($event->isRoleEnabled(ParticipantRole::Ist));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Guest));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::OrganizingTeam));
    }

    public function testGuestRoleFollowsAllowGuests(): void
    {
        $event = new Event();
        $event->allowPatrols = false;
        $event->allowTroops = false;
        $event->allowIsts = false;
        $event->allowGuests = true;
        $event->allowOrganizingTeam = false;

        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Ist));
        self::assertTrue($event->isRoleEnabled(ParticipantRole::Guest));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::OrganizingTeam));
    }

    public function testOrganizingTeamRoleFollowsAllowOrganizingTeam(): void
    {
        $event = new Event();
        $event->allowPatrols = false;
        $event->allowTroops = false;
        $event->allowIsts = false;
        $event->allowGuests = false;
        $event->allowOrganizingTeam = true;

        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::PatrolParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopLeader));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::TroopParticipant));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Ist));
        self::assertFalse($event->isRoleEnabled(ParticipantRole::Guest));
        self::assertTrue($event->isRoleEnabled(ParticipantRole::OrganizingTeam));
    }
}
