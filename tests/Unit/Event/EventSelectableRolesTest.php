<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Event\Event;
use kissj\Participant\ParticipantRole;
use PHPUnit\Framework\TestCase;

class EventSelectableRolesTest extends TestCase
{
    public function testPatrolsOnly(): void
    {
        $event = $this->createEvent(patrols: true);

        self::assertSame(['pl'], $this->values($event, false));
    }

    public function testTroopsOnly(): void
    {
        $event = $this->createEvent(troops: true);

        self::assertSame(['tl', 'tp'], $this->values($event, false));
    }

    public function testOrganizingTeamIsHiddenWithoutAccess(): void
    {
        $event = $this->createEvent(ists: true, organizingTeam: true);

        self::assertSame(['ist'], $this->values($event, false));
    }

    public function testOrganizingTeamIsFirstWithAccess(): void
    {
        $event = $this->createEvent(ists: true, organizingTeam: true);

        self::assertSame(['ot', 'ist'], $this->values($event, true));
    }

    public function testAllRolesWithAccess(): void
    {
        $event = $this->createEvent(true, true, true, true, true);

        self::assertSame(['ot', 'pl', 'tl', 'tp', 'ist', 'guest'], $this->values($event, true));
    }

    public function testTechnicalTeamLeaderIsHiddenWithoutAccess(): void
    {
        $event = $this->createEvent(ists: true, technicalTeams: true);

        self::assertSame(['ist'], $this->values($event, false, false));
    }

    public function testTechnicalTeamLeaderFollowsOrganizingTeamWithAccess(): void
    {
        $event = $this->createEvent(ists: true, organizingTeam: true, technicalTeams: true);

        self::assertSame(['ot', 'ttl', 'ist'], $this->values($event, true, true));
    }

    public function testTechnicalTeamLeaderNeedsTechnicalTeamsAllowed(): void
    {
        $event = $this->createEvent(ists: true);

        self::assertSame(['ist'], $this->values($event, false, true));
    }

    public function testNothingEnabled(): void
    {
        $event = $this->createEvent();

        self::assertSame([], $this->values($event, true));
    }

    private function createEvent(
        bool $patrols = false,
        bool $troops = false,
        bool $ists = false,
        bool $guests = false,
        bool $organizingTeam = false,
        bool $technicalTeams = false,
    ): Event {
        $event = new Event();
        $event->allowPatrols = $patrols;
        $event->allowTroops = $troops;
        $event->allowIsts = $ists;
        $event->allowGuests = $guests;
        $event->allowOrganizingTeam = $organizingTeam;
        $event->allowTechnicalTeams = $technicalTeams;

        return $event;
    }

    /**
     * @return list<string>
     */
    private function values(Event $event, bool $otAccessGranted, bool $ttlAccessGranted = false): array
    {
        return array_values(array_map(
            fn (ParticipantRole $role): string => $role->value,
            $event->getSelectableRoles($otAccessGranted, $ttlAccessGranted),
        ));
    }
}
