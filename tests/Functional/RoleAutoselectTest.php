<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class RoleAutoselectTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testOnlyIstEventAutoselectsIst(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, ists: true);
        $user = $this->registerAndLogin($app, 'autoselect-ist-only@example.com');

        $response = $this->chooseRole();

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('getDashboard', $response->getHeaderLine('Location'));
        $this->assertParticipantRole($user, 'ist');
        self::assertSame(UserStatus::Open, $this->reloadUser($user)->status);
    }

    public function testFirstLoginOnSingleRoleEventLandsOnDashboardWithoutChooseRoleFlash(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, ists: true);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);
        $user = $this->getService($app, UserService::class)
            ->registerEmailUser('autoselect-first-login@example.com', $event);
        $loginToken = $this->createLoginToken($app, $user);

        $response = $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/tryLogin/' . $loginToken->token,
        ));
        $visited = [];
        while ($response->getStatusCode() === 302 && count($visited) < 6) {
            $path = (string)parse_url($response->getHeaderLine('Location'), PHP_URL_PATH);
            $visited[] = $path;
            $response = $this->getTestApp(false)->handle($this->createRequest($path));
        }

        $translator = $this->getService($this->getTestApp(false), TranslatorInterface::class);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('getDashboard', implode(' ', $visited));
        self::assertStringNotContainsString(
            $translator->trans('flash.info.chooseRoleNeeded'),
            (string)$response->getBody(),
        );
        $this->assertParticipantRole($user, 'ist');
    }

    public function testIstWithOtButNoAccessAutoselectsIst(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, ists: true, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-ist-ot@example.com');

        $response = $this->chooseRole();

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('getDashboard', $response->getHeaderLine('Location'));
        $this->assertParticipantRole($user, 'ist');
    }

    public function testOtOnlyWithoutAccessRendersChooserWithoutParticipant(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-ot-only@example.com');

        $response = $this->chooseRole();

        self::assertSame(200, $response->getStatusCode());
        $this->assertNoParticipant($user);
        self::assertSame(UserStatus::WithoutRole, $this->reloadUser($user)->status);
        $translator = $this->getService($this->getTestApp(false), TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('flash.info.noRoleAvailable'),
            (string)$response->getBody(),
        );
    }

    public function testOtOnlyWithAccessAutoselectsOt(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-ot-access@example.com');
        $_SESSION['ot_access_granted'] = true;

        $response = $this->chooseRole();

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('getDashboard', $response->getHeaderLine('Location'));
        $this->assertParticipantRole($user, 'ot');
    }

    public function testOtAndIstWithAccessRenderChooser(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, ists: true, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-ot-ist-access@example.com');
        $_SESSION['ot_access_granted'] = true;

        $response = $this->chooseRole();

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('value="ot"', $body);
        self::assertStringContainsString('value="ist"', $body);
        $this->assertNoParticipant($user);
    }

    public function testPatrolsAndIstsRenderChooser(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, patrols: true, ists: true, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-pl-ist@example.com');

        $response = $this->chooseRole();

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('value="pl"', $body);
        self::assertStringContainsString('value="ist"', $body);
        self::assertStringNotContainsString('value="ot"', $body);
        $this->assertNoParticipant($user);
    }

    public function testSetRoleRejectsUnknownRole(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, patrols: true, ists: true);
        $user = $this->registerAndLogin($app, 'autoselect-unknown-role@example.com');

        $response = $this->postRole('xyz');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipant($user);
        $this->assertFlash('error', 'flash.error.roleNotAvailable');
    }

    public function testSetRoleRejectsPatrolParticipant(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, patrols: true, ists: true);
        $user = $this->registerAndLogin($app, 'autoselect-pp-role@example.com');

        $response = $this->postRole('pp');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipant($user);
        $this->assertFlash('error', 'flash.error.roleNotAvailable');
    }

    public function testSetRoleRejectsOtWithoutAccess(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, ists: true, organizingTeam: true);
        $user = $this->registerAndLogin($app, 'autoselect-ot-denied@example.com');

        $response = $this->postRole('ot');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipant($user);
        $this->assertFlash('error', 'flash.error.otAccessDenied');
    }

    public function testSetRoleRejectsDisabledRole(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, patrols: true, ists: true);
        $user = $this->registerAndLogin($app, 'autoselect-disabled-role@example.com');

        $response = $this->postRole('guest');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipant($user);
        $this->assertFlash('error', 'flash.error.roleNotAvailable');
    }

    public function testSetRoleAcceptsSelectableRole(): void
    {
        $app = $this->getTestApp();
        $this->setEnabledRoles($app, patrols: true, ists: true);
        $user = $this->registerAndLogin($app, 'autoselect-valid-role@example.com');

        $response = $this->postRole('ist');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('getDashboard', $response->getHeaderLine('Location'));
        $this->assertParticipantRole($user, 'ist');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setEnabledRoles(
        App $app,
        bool $patrols = false,
        bool $troops = false,
        bool $ists = false,
        bool $guests = false,
        bool $organizingTeam = false,
    ): void {
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);
        $this->mutateEventForTest($container, $event, [
            'allowPatrols' => $patrols,
            'allowTroops' => $troops,
            'allowIsts' => $ists,
            'allowGuests' => $guests,
            'allowOrganizingTeam' => $organizingTeam,
        ]);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerAndLogin(App $app, string $email): User
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG) ?? $eventRepository->get(1);
        $user = $this->getService($app, UserService::class)->registerEmailUser($email, $event);
        $loginToken = $this->createLoginToken($app, $user);

        $app->handle($this->createRequest(self::BASE_URL . '/tryLogin/' . $loginToken->token));

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createLoginToken(App $app, User $user): LoginToken
    {
        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $this->getService($app, LoginTokenRepository::class)->persist($loginToken);

        return $loginToken;
    }

    private function chooseRole(): ResponseInterface
    {
        return $this->getTestApp(false)->handle($this->createRequest(self::BASE_URL . '/chooseRole'));
    }

    private function postRole(string $role): ResponseInterface
    {
        return $this->getTestApp(false)->handle($this->createRequest(
            self::BASE_URL . '/setRole',
            'POST',
            ['role' => $role],
        ));
    }

    private function reloadUser(User $user): User
    {
        return $this->getService($this->getTestApp(false), UserRepository::class)->get($user->id);
    }

    private function assertParticipantRole(User $user, string $role): void
    {
        $participant = $this->getService($this->getTestApp(false), ParticipantRepository::class)
            ->findParticipantFromUser($this->reloadUser($user));

        self::assertNotNull($participant);
        self::assertSame($role, $participant->role?->value);
    }

    private function assertNoParticipant(User $user): void
    {
        self::assertNull(
            $this->getService($this->getTestApp(false), ParticipantRepository::class)
                ->findParticipantFromUser($this->reloadUser($user)),
        );
    }

    private function assertFlash(string $type, string $key): void
    {
        $translator = $this->getService($this->getTestApp(false), TranslatorInterface::class);
        $expected = ['type' => $type, 'message' => $translator->trans($key)];

        $messages = $_SESSION['flashMessages'] ?? [];
        self::assertIsArray($messages);
        self::assertContains($expected, $messages);
    }
}
