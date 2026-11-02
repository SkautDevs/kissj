<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Authorization\AdminRouteAccessResolver;
use kissj\User\UserRole;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Tests\AppTestCase;

class AdminRouteAccessTest extends AppTestCase
{
    private const array IST_ADMIN_ALLOWED = [
        'admin-change-note',
        'admin-change-participant-details',
        'admin-dashboard',
        'admin-entry-participant',
        'admin-mend-participant',
        'admin-set-deal-as-done',
        'admin-show-open',
        'admin-show-participant-details-changeable',
        'admin-show-stats',
        'admin-unentry-participant',
        'admin-unleave-participant',
    ];

    private const array CONTINGENT_ADMIN_DENIED = [
        'admin-badges-form',
        'admin-export-blank-badges',
        'admin-export-full-badges',
        'admin-organizing-team',
        'admin-organizing-team-regenerate-token',
        'admin-role-management',
        'admin-save-role-management',
        'admin-technical-teams',
        'admin-technical-teams-regenerate-token',
        'admin-values-change',
        'admin-values-import',
        'admin-values-import-run',
    ];

    public function testIstAdminIsAllowedExactlyTheListedRoutes(): void
    {
        $app = $this->getTestApp();
        $resolver = $this->getService($app, AdminRouteAccessResolver::class);
        $istAdmin = $this->createAdminUser($app, UserRole::IstAdmin);

        $allowed = array_values(array_filter(
            $this->getAdminMiddlewareRouteNames($app),
            static fn (string $name): bool => $resolver->canAccessRoute($istAdmin, $name),
        ));
        sort($allowed);

        self::assertSame(self::IST_ADMIN_ALLOWED, $allowed);
    }

    public function testContingentAdminIsDeniedExactlyTheListedRoutes(): void
    {
        $app = $this->getTestApp();
        $resolver = $this->getService($app, AdminRouteAccessResolver::class);
        $contingentAdmin = $this->createAdminUser($app, UserRole::ContingentAdminCs);

        $denied = array_values(array_filter(
            $this->getAdminMiddlewareRouteNames($app),
            static fn (string $name): bool => $resolver->canAccessRoute($contingentAdmin, $name) === false,
        ));
        sort($denied);

        self::assertSame(self::CONTINGENT_ADMIN_DENIED, $denied);
    }

    public function testTopAdminPassesEveryAdminRoute(): void
    {
        $app = $this->getTestApp();
        $resolver = $this->getService($app, AdminRouteAccessResolver::class);
        $admin = $this->createAdminUser($app, UserRole::Admin);

        foreach ($this->getAdminMiddlewareRouteNames($app) as $name) {
            self::assertTrue($resolver->canAccessRoute($admin, $name), $name);
        }
    }

    public function testIstAdminDeniedRedirectsToAdminDashboard(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/approving'));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    public function testIstAdminReachesOpenList(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/showOpen'));

        self::assertSame(200, $response->getStatusCode());
    }

    public function testContingentAdminDeniedRoleManagement(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);

        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/roleManagement'));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    public function testJsonApproveDeniedForIstAdmin(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $ist = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);
        $app = $this->loginAdminForEvent($app, $event, UserRole::IstAdmin);

        $response = $app->handle($this->createRequest(
            '/v3/event/' . $event->slug . '/admin/approving/approveParticipant/' . $ist->id,
            'POST',
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<string>
     */
    private function getAdminMiddlewareRouteNames(App $app): array
    {
        return array_values(array_filter(
            array_map(
                static fn (RouteInterface $route): ?string => $route->getName(),
                $app->getRouteCollector()->getRoutes(),
            ),
            static fn (?string $name): bool => $name !== null
                && str_starts_with($name, 'admin-')
                && str_starts_with($name, 'admin-programme') === false
                && str_starts_with($name, 'admin-export-programmes') === false,
        ));
    }
}
