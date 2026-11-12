<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\RoleChangeResult;
use kissj\Participant\Troop\TroopParticipant;
use kissj\User\UserService;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Tests\AppTestCase;

class ParticipantOwnRoleChangeTest extends AppTestCase
{
    public function testDashboardOffersRoleChangeToOpenParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $this->loginAs($this->createParticipantWithRole($app->getContainer(), 'ist', $event));

        $response = $this->send($app, $event, 'dashboard');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString($this->urlFor($app, 'chooseRole', $event), (string)$response->getBody());
    }

    public function testDashboardHidesRoleChangeWhenClosed(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->getService($app, UserService::class)->setUserClosed($ist->getUserButNotNull());
        $this->loginAs($ist);

        $response = $this->send($app, $event, 'dashboard');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString($this->urlFor($app, 'chooseRole', $event), (string)$response->getBody());
    }

    public function testDashboardHidesRoleChangeOnSingleRoleEvent(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['allowPatrols' => false, 'allowTroops' => false]);
        $this->loginAs($this->createParticipantWithRole($app->getContainer(), 'ist', $event));

        $response = $this->send($app, $event, 'dashboard');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString($this->urlFor($app, 'chooseRole', $event), (string)$response->getBody());
    }

    public function testRolePageMarksCurrentRole(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $this->loginAs($this->createParticipantWithRole($app->getContainer(), 'ist', $event));

        $response = $this->send($app, $event, 'chooseRole');

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('action="' . $this->urlFor($app, 'setRole', $event) . '"', $body);
        self::assertStringContainsString('value="pl"', $body);
        self::assertStringContainsString('value="ist"', $body);
        self::assertSame(['ist'], $this->getRolesMarkedCurrent($app, $body));
        self::assertStringContainsString($this->urlFor($app, 'getDashboard', $event), $body);
    }

    public function testOpenParticipantChangesRoleKeepingData(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $ist->firstName = 'Karel';
        $this->getService($app, ParticipantRepository::class)->persist($ist);
        $this->loginAs($ist);
        $this->expectLog(Level::Info, 'Participant changed own role');

        $response = $this->send($app, $event, 'setRole', ['role' => 'pl']);

        $this->assertRedirectedToDashboard($app, $event, $response);
        $fresh = $this->getFreshParticipant($app, $ist);
        self::assertSame('pl', $fresh->role?->value);
        self::assertSame('Karel', $fresh->firstName);
        $flashes = $this->flashed($app);
        self::assertCount(1, $flashes);
        self::assertStringContainsString($this->translate($app, 'role.pl'), $flashes[0]);
        $this->assertSingleParticipantRow($app, $ist);
    }

    public function testPickingCurrentRoleChangesNothing(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->loginAs($ist);

        $response = $this->send($app, $event, 'setRole', ['role' => 'ist']);

        $this->assertRedirectedToDashboard($app, $event, $response);
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
        self::assertSame([], $this->flashed($app));
    }

    public function testPatrolLeaderWithMembersIsBlockedOnPageAndPost(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $this->tieToPatrol($app, $this->createParticipantWithRole($app->getContainer(), 'pp', $event), $leader);
        $this->loginAs($leader);
        $warning = $this->translate($app, 'flash.warning.ownRoleLeaderHasParticipants');

        $pageResponse = $this->send($app, $event, 'chooseRole');

        $this->assertRedirectedToDashboard($app, $event, $pageResponse);
        self::assertSame([$warning], $this->flashed($app));

        $this->send($app, $event, 'setRole', ['role' => 'ist']);

        self::assertSame('pl', $this->getFreshParticipant($app, $leader)->role?->value);
        self::assertSame([$warning], $this->flashed($app));
    }

    public function testBlockedLeaderPostingUnavailableRoleGetsOnlyBlockWarning(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $this->tieToPatrol($app, $this->createParticipantWithRole($app->getContainer(), 'pp', $event), $leader);
        $this->loginAs($leader);

        $response = $this->send($app, $event, 'setRole', ['role' => 'ot']);

        $this->assertRedirectedToDashboard($app, $event, $response);
        self::assertSame('pl', $this->getFreshParticipant($app, $leader)->role?->value);
        self::assertSame(
            [$this->translate($app, 'flash.warning.ownRoleLeaderHasParticipants')],
            $this->flashed($app),
        );
    }

    public function testTroopLeaderWithMembersIsBlocked(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $this->tieToTroop($app, $this->createParticipantWithRole($app->getContainer(), 'tp', $event), $leader);
        $this->loginAs($leader);

        $this->send($app, $event, 'setRole', ['role' => 'ist']);

        self::assertSame('tl', $this->getFreshParticipant($app, $leader)->role?->value);
        self::assertSame(
            [$this->translate($app, 'flash.warning.ownRoleLeaderHasParticipants')],
            $this->flashed($app),
        );
    }

    public function testTiedTroopParticipantIsBlocked(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $this->tieToTroop($app, $member, $leader);
        $this->loginAs($member);

        $this->send($app, $event, 'setRole', ['role' => 'ist']);

        $fresh = $this->getFreshParticipant($app, $member);
        self::assertInstanceOf(TroopParticipant::class, $fresh);
        self::assertSame('tp', $fresh->role?->value);
        self::assertSame($leader->id, $fresh->troopLeader?->id);
        self::assertSame(
            [$this->translate($app, 'flash.warning.ownRoleTroopParticipantInTroop')],
            $this->flashed($app),
        );
    }

    public function testChangeIntoFullRoleIsAllowed(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['maximalClosedIstsCount' => 0]);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $this->loginAs($leader);
        $this->expectLog(Level::Info, 'Participant changed own role');

        $response = $this->send($app, $event, 'setRole', ['role' => 'ist']);

        $this->assertRedirectedToDashboard($app, $event, $response);
        self::assertSame('ist', $this->getFreshParticipant($app, $leader)->role?->value);
        $flashes = $this->flashed($app);
        self::assertCount(1, $flashes);
        self::assertStringContainsString($this->translate($app, 'role.ist'), $flashes[0]);
    }

    public function testClosedParticipantCannotChangeRole(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->getService($app, UserService::class)->setUserClosed($ist->getUserButNotNull());
        $this->loginAs($ist);

        $pageResponse = $this->send($app, $event, 'chooseRole');

        self::assertSame(302, $pageResponse->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.warning.roleChoosed')], $this->flashed($app));

        $this->send($app, $event, 'setRole', ['role' => 'pl']);

        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
        $this->assertSingleParticipantRow($app, $ist);
    }

    public function testSingleRoleEventNeverCreatesSecondParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['allowPatrols' => false, 'allowTroops' => false]);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->loginAs($ist);

        $response = $this->send($app, $event, 'chooseRole');

        $this->assertRedirectedToDashboard($app, $event, $response);
        $this->assertSingleParticipantRow($app, $ist);
    }

    public function testOrganizingTeamCurrentRoleShownWithoutAccessFlag(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['allowOrganizingTeam' => true]);
        $this->loginAs($this->createParticipantWithRole($app->getContainer(), 'ot', $event));

        $pageResponse = $this->send($app, $event, 'chooseRole');

        self::assertSame(200, $pageResponse->getStatusCode());
        $body = (string)$pageResponse->getBody();
        self::assertSame(['ot'], $this->getRolesMarkedCurrent($app, $body));
        self::assertLessThan(strpos($body, 'value="pl"'), strpos($body, 'value="ot"'));

        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->loginAs($ist);

        $this->send($app, $event, 'setRole', ['role' => 'ot']);

        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
        self::assertSame([$this->translate($app, 'flash.error.otAccessDenied')], $this->flashed($app));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function tamperedRoleProvider(): iterable
    {
        yield 'patrol participant' => ['pp', 'flash.error.roleNotAvailable'];
        yield 'technical team participant' => ['ttp', 'flash.error.ttlAccessDenied'];
        yield 'technical team leader without access flag' => ['ttl', 'flash.error.ttlAccessDenied'];
        yield 'gibberish' => ['gibberish', 'flash.error.roleNotAvailable'];
    }

    #[DataProvider('tamperedRoleProvider')]
    public function testTamperedRoleIsRejected(string $postedRole, string $expectedFlashKey): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app, ['allowTechnicalTeams' => true]);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $this->loginAs($ist);

        $response = $this->send($app, $event, 'setRole', ['role' => $postedRole]);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'chooseRole', $event), $response->getHeaderLine('Location'));
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
        self::assertSame([$this->translate($app, $expectedFlashKey)], $this->flashed($app));
        $this->assertSingleParticipantRow($app, $ist);
    }

    public function testOwnRoleChangeOnStaleOpenParticipantIsRefusedAfterClose(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $staleIst = $this->getFreshParticipant($app, $ist);
        $this->getService($app, UserService::class)->setUserClosed($ist->getUserButNotNull());

        $result = $this->getService($app, ParticipantService::class)->tryChangeOwnRole($staleIst, 'pl');

        self::assertSame(RoleChangeResult::NotOpen, $result);
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
    }

    public function testServiceRefusesOwnRoleChangeOfLeaderWithMembers(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'pl', $event);
        $this->tieToPatrol($app, $this->createParticipantWithRole($app->getContainer(), 'pp', $event), $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeOwnRole($this->getFreshParticipant($app, $leader), 'ist');

        self::assertSame(RoleChangeResult::PatrolHasParticipants, $result);
        self::assertSame('pl', $this->getFreshParticipant($app, $leader)->role?->value);
    }

    public function testServiceRefusesOwnRoleChangeOfTiedTroopParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $member = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $this->tieToTroop($app, $member, $leader);

        $result = $this->getService($app, ParticipantService::class)
            ->tryChangeOwnRole($this->getFreshParticipant($app, $member), 'ist');

        self::assertSame(RoleChangeResult::TroopParticipantInTroop, $result);
        self::assertSame('tp', $this->getFreshParticipant($app, $member)->role?->value);
    }

    #[DataProvider('unselectableRoleProvider')]
    public function testServiceRefusesUnselectableOwnRole(string $postedRole): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        $result = $this->getService($app, ParticipantService::class)->tryChangeOwnRole($ist, $postedRole);

        self::assertSame(RoleChangeResult::RoleNotValid, $result);
        self::assertSame('ist', $this->getFreshParticipant($app, $ist)->role?->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unselectableRoleProvider(): iterable
    {
        yield 'patrol participant' => ['pp'];
        yield 'organizing team' => ['ot'];
        yield 'gibberish' => ['gibberish'];
    }

    public function testOpenAdminIsKeptAwayFromRolePage(): void
    {
        $app = $this->getTestApp();
        $event = $this->prepareEvent($app);
        $this->loginAdminForEvent($app, $event);

        $response = $this->send($app, $event, 'chooseRole');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.warning.roleChoosed')], $this->flashed($app));
    }

    /**
     * @param App<ContainerInterface> $app
     * @param array<string, bool|int> $overrides
     */
    private function prepareEvent(App $app, array $overrides = []): Event
    {
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, array_merge([
            'allowPatrols' => true,
            'allowTroops' => true,
            'allowIsts' => true,
            'allowGuests' => false,
            'allowOrganizingTeam' => false,
            'allowTechnicalTeams' => false,
        ], $overrides));

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<string>
     */
    private function getRolesMarkedCurrent(App $app, string $body): array
    {
        $marker = $this->translate($app, 'chooseRole.currentRole');
        preg_match_all('#<form\b.*?</form>#s', $body, $forms);
        $markedForms = array_filter(
            $forms[0],
            fn (string $form): bool => preg_match('#<button type="submit"[^>]*>(.*?)</button>#s', $form, $button) === 1
                && str_contains($button[1], '<br><span class="text-regular">' . $marker . '</span>'),
        );

        return array_values(array_map(
            fn (string $form): string => preg_match('#name="role" value="(\w+)"#', $form, $match) === 1
                ? $match[1]
                : '',
            $markedForms,
        ));
    }

    private function loginAs(Participant $participant): void
    {
        $_SESSION['user'] = ['id' => $participant->getUserButNotNull()->id];
    }

    /**
     * @param App<ContainerInterface> $app
     * @param array<string, string> $body
     */
    private function send(App $app, Event $event, string $routeName, array $body = []): ResponseInterface
    {
        return $this->getTestApp(false)->handle($this->createRequest(
            $this->urlFor($app, $routeName, $event),
            $body === [] ? 'GET' : 'POST',
            $body,
        ));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function assertRedirectedToDashboard(App $app, Event $event, ResponseInterface $response): void
    {
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'getDashboard', $event), $response->getHeaderLine('Location'));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function assertSingleParticipantRow(App $app, Participant $participant): void
    {
        self::assertSame(
            1,
            $this->getService($app, ParticipantRepository::class)
                ->countBy(['user' => $participant->getUserButNotNull()]),
        );
    }
}
