<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

// Locks the security boundary introduced when ProgrammeAdmin was narrowed to a
// programme-only role: it must reach the programme admin section but nothing
// else in /admin, while a full Admin must keep reaching both.
class ProgrammeAdminScopeTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testProgrammeAdminReachesProgrammeSectionButNotRestOfAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedUser($app, 'programme-admin-scope@example.com', UserRole::ProgrammeAdmin);

        $responseProgrammes = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes',
        ));
        self::assertSame(200, $responseProgrammes->getStatusCode());

        $responseDashboard = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/dashboard',
        ));
        self::assertNotSame(200, $responseDashboard->getStatusCode());

        $responseExportFull = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/export/full',
        ));
        self::assertNotSame(200, $responseExportFull->getStatusCode());
    }

    public function testFullAdminStillReachesProgrammeSection(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedUser($app, 'full-admin-scope@example.com', UserRole::Admin);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/programmes',
        ));
        self::assertSame(200, $response->getStatusCode());
    }

    public function testAdminDashboardLinksToProgrammeSectionForFullAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedUser($app, 'full-admin-dashboard-link@example.com', UserRole::Admin);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            'href="/v2/event/test-event-slug/admin/programmes"',
            (string)$response->getBody(),
        );
    }

    public function testAdminDashboardHidesProgrammeLinkFromIstAdmin(): void
    {
        $app = $this->getTestApp();
        $app = $this->createLoggedUser($app, 'ist-admin-dashboard-link@example.com', UserRole::IstAdmin);

        $response = $app->handle($this->createRequest(
            '/v2/event/test-event-slug/admin/dashboard',
        ));
        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString(
            'href="/v2/event/test-event-slug/admin/programmes"',
            (string)$response->getBody(),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function createLoggedUser(App $app, string $email, UserRole $role): App
    {
        $user = $this->registerUser($app, $email);
        $userRepository = $this->getService($app, UserRepository::class);
        $user->role = $role;
        $userRepository->persist($user);

        return $this->loginAs($app, $user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerUser(App $app, string $email): User
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function loginAs(App $app, User $user): App
    {
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . self::TEST_EVENT_SLUG . '/tryLogin/' . $loginToken->token,
        ));
        self::assertSame(302, $response->getStatusCode());

        // the container (and the UserRegeneration singleton reading $_SESSION at construction
        // time) is scoped to one $app instance, so a fresh one is needed to pick up the login
        return $this->getTestApp(false);
    }
}
