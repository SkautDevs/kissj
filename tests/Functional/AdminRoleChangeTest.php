<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\RoleChangeResult;
use kissj\Participant\Troop\TroopLeader;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminRoleChangeTest extends AppTestCase
{
    public function testOpenIstChangesToPatrolLeaderKeepingData(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $ist->firstName = 'Karel';
        $this->getService($app, ParticipantRepository::class)->persist($ist);

        $result = $this->getService($app, ParticipantService::class)->tryChangeRole('pl', $ist, $event);

        $fresh = $this->getFreshParticipant($app, $ist);
        self::assertSame(RoleChangeResult::Success, $result);
        self::assertSame('pl', $fresh->role?->value);
        self::assertSame('Karel', $fresh->firstName);
    }

    public function testPatrolLeaderWithMembersIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'pp', $event);
        $this->tieToPatrol($app, $member, $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeRole('ist', $this->getFreshParticipant($app, $leader), $event);

        self::assertSame(RoleChangeResult::PatrolHasParticipants, $result);
        self::assertSame('pl', $this->getFreshParticipant($app, $leader)->role?->value);
    }

    public function testTroopLeaderWithMembersIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $this->tieToTroop($app, $member, $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeRole('ist', $this->getFreshParticipant($app, $leader), $event);

        self::assertSame(RoleChangeResult::TroopHasParticipants, $result);
        self::assertSame('tl', $this->getFreshParticipant($app, $leader)->role?->value);
    }

    public function testSameRoleIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $result = $this->getService($app, ParticipantService::class)->tryChangeRole('ist', $ist, $event);

        self::assertSame(RoleChangeResult::SameRole, $result);
    }

    public function testNotOpenIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->getService($app, UserService::class)->setUserClosed($ist->getUserButNotNull());

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeRole('pl', $this->getFreshParticipant($app, $ist), $event);

        self::assertSame(RoleChangeResult::NotOpen, $result);
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
    }

    public function testRoleNotInEventIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $this->mutateEventForTest($app->getContainer(), $event, ['allowPatrols' => false]);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $result = $this->getService($app, ParticipantService::class)->tryChangeRole('pl', $ist, $event);

        self::assertSame(RoleChangeResult::RoleNotValid, $result);
    }

    public function testTiedTroopParticipantIsUntied(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $this->tieToTroop($app, $member, $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeRole('ist', $this->getFreshParticipant($app, $member), $event);

        self::assertSame(RoleChangeResult::Success, $result);
        self::assertSame('ist', $this->getFreshParticipant($app, $member)->role?->value);
        $freshLeader = $this->getFreshParticipant($app, $leader);
        self::assertInstanceOf(TroopLeader::class, $freshLeader);
        self::assertSame(0, $freshLeader->getTroopParticipantsCount());
    }

    public function testPatrolParticipantIsRefusedAndStaysInPatrol(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'pp', $event);
        $this->tieToPatrol($app, $member, $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeRole('ist', $this->getFreshParticipant($app, $member), $event);

        self::assertSame(RoleChangeResult::RoleNotValid, $result);
        $freshMember = $this->getFreshParticipant($app, $member);
        self::assertInstanceOf(PatrolParticipant::class, $freshMember);
        self::assertSame($leader->id, $freshMember->patrolLeader->id);
    }

    public function testChangeToPatrolParticipantIsRefused(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $result = $this->getService($app, ParticipantService::class)->tryChangeRole('pp', $ist, $event);

        self::assertSame(RoleChangeResult::RoleNotValid, $result);
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
    }

    public function testOpenListHidesChangeRoleLinkForPatrolParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'pp', $event);
        $this->tieToPatrol($app, $member, $leader);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $leader->firstName = 'Leader';
        $participantRepository->persist($leader);
        $freshMember = $this->getFreshParticipant($app, $member);
        $freshMember->firstName = 'Member';
        $participantRepository->persist($freshMember);

        $body = $this->fetchAdminPage($app, $event, '/admin/showOpen');

        self::assertStringContainsString('/changeRole/' . $leader->id . '/show', $body);
        self::assertStringNotContainsString('/changeRole/' . $member->id . '/show', $body);
    }

    public function testChangeRolePageDoesNotOfferPatrolParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->enableAllRoles($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $body = $this->fetchAdminPage($app, $event, '/admin/changeRole/' . $ist->id . '/show');

        self::assertStringContainsString('value="pl"', $body);
        self::assertStringNotContainsString('value="pp"', $body);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function fetchAdminPage(App $app, Event $event, string $path): string
    {
        $response = $this->loginAdminForEvent($app, $event)
            ->handle($this->createRequest('/v2/event/' . $event->slug . $path));
        self::assertSame(200, $response->getStatusCode(), $path);

        return (string)$response->getBody();
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function enableAllRoles(App $app): Event
    {
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest(
            $app->getContainer(),
            $event,
            ['allowPatrols' => true, 'allowTroops' => true, 'allowIsts' => true],
        );

        return $event;
    }
}
