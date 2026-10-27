<?php

declare(strict_types=1);

namespace Tests\Functional;

use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteResolverInterface;
use Tests\AppTestCase;
use Throwable;

class RouteGuardTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';

    public const array PUBLIC_EXACT_PATTERNS = [
        '/',
        '/v2',
        '/v2/kissj/events',
        '/v2/kissj/skautisRedirect',
        '/v2/event/{eventSlug}',
        '/v2/event/{eventSlug}/tryLogin/{token}',
        '/v2/event/{eventSlug}/login',
        '/v2/event/{eventSlug}/loginAfterLinkSent',
        '/v2/event/{eventSlug}/loginHelp',
        '/{eventSlug}',
    ];

    private const string PUBLIC_PATH_PREFIX = '/v3/';

    public function testAnonymousRequestIsBlockedOnEveryNonPublicRoute(): void
    {
        $app = $this->getTestApp();
        $loginUrl = $app->getRouteCollector()->getRouteParser()
            ->urlFor('loginAskEmail', ['eventSlug' => self::EVENT_SLUG]);

        $failures = [];
        $checkedCount = 0;
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            if ($this->isPublic($route->getPattern())) {
                continue;
            }

            $path = $this->fillPlaceholders($route->getPattern());
            foreach ($route->getMethods() as $method) {
                if ($method === 'OPTIONS') {
                    continue;
                }

                $checkedCount++;
                $failure = $this->findBlockFailure($app->getRouteResolver(), $route, $method, $path, $loginUrl);
                if ($failure !== null) {
                    $failures[] = $failure;
                }
            }
        }

        self::assertSame([], $failures, 'anonymous request was not blocked on a non-public route');
        self::assertGreaterThanOrEqual(
            100,
            $checkedCount,
            'too few routes checked, the route list or allowlist is broken',
        );
    }

    public function testEveryPublicAllowlistEntryMatchesARoute(): void
    {
        $patterns = array_map(
            static fn (RouteInterface $route): string => $route->getPattern(),
            array_values($this->getTestApp()->getRouteCollector()->getRoutes()),
        );

        $unmatched = [];
        foreach (self::PUBLIC_EXACT_PATTERNS as $exactPattern) {
            if (in_array($exactPattern, $patterns, true) === false) {
                $unmatched[] = $exactPattern;
            }
        }

        $prefixMatches = array_filter(
            $patterns,
            static fn (string $pattern): bool => str_starts_with($pattern, self::PUBLIC_PATH_PREFIX),
        );
        if ($prefixMatches === []) {
            $unmatched[] = self::PUBLIC_PATH_PREFIX . '*';
        }

        self::assertSame([], $unmatched, 'public allowlist entry matches no route');
    }

    private function isPublic(string $pattern): bool
    {
        return in_array($pattern, self::PUBLIC_EXACT_PATTERNS, true)
            || str_starts_with($pattern, self::PUBLIC_PATH_PREFIX);
    }

    private function findBlockFailure(
        RouteResolverInterface $routeResolver,
        RouteInterface $route,
        string $method,
        string $path,
        string $loginUrl,
    ): ?string {
        $label = sprintf('%s %s %s', $route->getName() ?? '(unnamed)', $method, $path);

        $routeMismatch = $this->findRouteMismatch($routeResolver, $route, $method, $path, $label);
        if ($routeMismatch !== null) {
            return $routeMismatch;
        }

        $_SESSION = [];
        try {
            $response = $this->getTestApp(false)->handle($this->createRequest($path, $method));
        } catch (Throwable $exception) {
            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        $location = $response->getHeaderLine('Location');
        if ($status === 401 || ($status === 302 && $location === $loginUrl)) {
            return null;
        }

        return sprintf('%s: got %d, Location "%s"', $label, $status, $location);
    }
}
