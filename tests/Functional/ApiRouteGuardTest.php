<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Psr7\Request;
use Tests\AppTestCase;
use Throwable;

class ApiRouteGuardTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string API_PATH_PREFIX = '/v3/';
    private const string UNKNOWN_KEY = 'route-guard-unknown-key';

    private const array KEYS = [
        'apiKeyEntry' => 'route-guard-entry-key',
        'apiKeyDeals' => 'route-guard-deals-key',
        'apiKeyVendor' => 'route-guard-vendor-key',
        'apiKeyVendorHealth' => 'route-guard-vendor-health-key',
    ];

    private const array WRONG_KEY_FIELDS_BY_GUARD = [
        'entry' => ['apiKeyDeals', 'apiKeyVendor', 'apiKeyVendorHealth'],
        'deal' => ['apiKeyEntry', 'apiKeyVendor', 'apiKeyVendorHealth'],
        'vendor' => ['apiKeyEntry', 'apiKeyDeals'],
        'adminSession' => ['apiKeyEntry', 'apiKeyDeals', 'apiKeyVendor', 'apiKeyVendorHealth'],
    ];

    private const array GUARD_BY_ROUTE_NAME = [
        'health' => 'public',
        'entry' => 'public',
        'deal-catch-data-from-google-form' => 'deal',
        'entry-list' => 'entry',
        'entry-participant-from-web-app' => 'entry',
        'entry-group-from-web-app' => 'entry',
        'leave-participant-from-web-app' => 'entry',
        'leave-group-from-web-app' => 'entry',
        'vendor-bearer-check' => 'vendor',
        'vendor-participant-by-tie-code' => 'vendor',
        'admin-change-note' => 'adminSession',
        'admin-approve-json' => 'adminSession',
        'admin-confirm-payment-json' => 'adminSession',
    ];

    public function testEveryGuardedApiRouteRejectsMissingAndWrongCredentials(): void
    {
        $app = $this->getTestApp();
        $this->setEventKeys($app);
        $loginUrl = $app->getRouteCollector()->getRouteParser()
            ->urlFor('loginAskEmail', ['eventSlug' => self::EVENT_SLUG]);

        $failures = [];
        $checkedCount = 0;
        foreach ($this->getApiRoutes($app) as $route) {
            $guard = self::GUARD_BY_ROUTE_NAME[$route->getName() ?? '']
                ?? throw new LogicException('v3 route ' . $route->getPattern() . ' is missing from the guard map');
            if ($guard === 'public') {
                continue;
            }

            $path = $this->fillPlaceholders($route->getPattern());
            foreach ($route->getMethods() as $method) {
                if ($method === 'OPTIONS') {
                    continue;
                }

                foreach ($this->getRejectedApiKeys($guard) as $apiKey) {
                    $checkedCount++;
                    $failure = $this->findRejectionFailure($app->getRouteResolver(), $route, $guard, $method, $path, $apiKey, $loginUrl);
                    if ($failure !== null) {
                        $failures[] = $failure;
                    }
                }
            }
        }

        self::assertSame([], $failures, 'api route accepted missing or wrong credentials');
        self::assertGreaterThanOrEqual(50, $checkedCount, 'too few requests checked, the route list or guard map is broken');
    }

    public function testEveryStoredApiKeyResolvesToTheTestEventOnly(): void
    {
        $app = $this->getTestApp();
        $this->setEventKeys($app);
        $eventRepository = $this->getService($app, EventRepository::class);

        self::assertSame(self::EVENT_SLUG, $eventRepository->findByEntryApiKey(self::KEYS['apiKeyEntry'])?->slug);
        self::assertSame(self::EVENT_SLUG, $eventRepository->findByDealApiKey(self::KEYS['apiKeyDeals'])?->slug);
        foreach ([self::KEYS['apiKeyVendor'], self::KEYS['apiKeyVendorHealth']] as $vendorKey) {
            $events = $eventRepository->findAllByVendorApiKey($vendorKey);
            self::assertCount(1, $events);
            self::assertSame(self::EVENT_SLUG, $events[0]->slug);
        }

        self::assertNull($eventRepository->findByEntryApiKey(self::UNKNOWN_KEY));
        self::assertNull($eventRepository->findByDealApiKey(self::UNKNOWN_KEY));
        self::assertSame([], $eventRepository->findAllByVendorApiKey(self::UNKNOWN_KEY));

        $allKeys = [...array_values(self::KEYS), self::UNKNOWN_KEY];
        self::assertNotContains('', $allKeys);
        self::assertCount(count($allKeys), array_unique($allKeys));
    }

    public function testAnonymousPreflightIsAnsweredByCorsMiddleware(): void
    {
        $app = $this->getTestApp();

        $failures = [];
        $checkedCount = 0;
        foreach ($this->getApiRoutes($app) as $route) {
            if (in_array('OPTIONS', $route->getMethods(), true) === false) {
                continue;
            }

            $checkedCount++;
            $path = $this->fillPlaceholders($route->getPattern());
            $failure = $this->findPreflightFailure($app->getRouteResolver(), $route, $path);
            if ($failure !== null) {
                $failures[] = $failure;
            }
        }

        self::assertSame([], $failures, 'anonymous preflight was not short-circuited by CORS middleware');
        self::assertGreaterThanOrEqual(7, $checkedCount, 'too few preflight routes checked');
    }

    public function testGuardMapMatchesApiRoutesExactly(): void
    {
        $routeNames = [];
        $unmapped = [];
        foreach ($this->getApiRoutes($this->getTestApp()) as $route) {
            $name = $route->getName();
            if ($name === null) {
                $unmapped[] = '(unnamed) ' . $route->getPattern();
                continue;
            }

            $routeNames[] = $name;
            if (array_key_exists($name, self::GUARD_BY_ROUTE_NAME) === false) {
                $unmapped[] = $name;
            }
        }

        $stale = array_values(array_diff(array_keys(self::GUARD_BY_ROUTE_NAME), $routeNames));

        self::assertSame([], $unmapped, 'v3 route is missing from the guard map');
        self::assertSame([], $stale, 'guard map entry names no v3 route');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setEventKeys(App $app): void
    {
        $event = $this->getService($app, EventRepository::class)->findBySlug(self::EVENT_SLUG)
            ?? throw new LogicException('seeded event ' . self::EVENT_SLUG . ' not found');

        $this->mutateEventForTest($app->getContainer(), $event, self::KEYS);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<RouteInterface>
     */
    private function getApiRoutes(App $app): array
    {
        return array_values(array_filter(
            $app->getRouteCollector()->getRoutes(),
            static fn (RouteInterface $route): bool => str_starts_with($route->getPattern(), self::API_PATH_PREFIX),
        ));
    }

    /**
     * @return list<?string>
     */
    private function getRejectedApiKeys(string $guard): array
    {
        $wrongKeyFields = self::WRONG_KEY_FIELDS_BY_GUARD[$guard]
            ?? throw new LogicException('unknown guard ' . $guard);
        $wrongKeys = array_map(static fn (string $field): string => self::KEYS[$field], $wrongKeyFields);

        if ($guard === 'adminSession') {
            return [null, ...$wrongKeys];
        }

        return [null, ...$wrongKeys, self::UNKNOWN_KEY];
    }

    private function createApiRequest(string $path, string $method, ?string $apiKey): Request
    {
        $request = $this->createRequest($path, $method);
        if ($apiKey === null) {
            return $request;
        }

        return $request->withHeader('Authorization', 'Bearer ' . $apiKey);
    }

    private function findRejectionFailure(
        RouteResolverInterface $routeResolver,
        RouteInterface $route,
        string $guard,
        string $method,
        string $path,
        ?string $apiKey,
        string $loginUrl,
    ): ?string {
        $label = sprintf('%s %s %s with key %s', $route->getName() ?? '(unnamed)', $method, $path, $apiKey ?? '(none)');

        $routeMismatch = $this->findRouteMismatch($routeResolver, $route, $method, $path, $label);
        if ($routeMismatch !== null) {
            return $routeMismatch;
        }

        $request = $this->createApiRequest($path, $method, $apiKey);
        $expectedAuthorization = $apiKey === null ? '' : 'Bearer ' . $apiKey;
        if ($request->getHeaderLine('Authorization') !== $expectedAuthorization) {
            return sprintf('%s: request carries Authorization "%s"', $label, $request->getHeaderLine('Authorization'));
        }

        $_SESSION = [];
        try {
            $response = $this->getTestApp(false)->handle($request);
        } catch (Throwable $exception) {
            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        $location = $response->getHeaderLine('Location');
        if ($guard === 'adminSession' && $status === 302 && $location === $loginUrl) {
            return null;
        }
        if ($guard !== 'adminSession' && $status === 401) {
            return null;
        }

        return sprintf('%s: got %d, Location "%s"', $label, $status, $location);
    }

    private function findPreflightFailure(
        RouteResolverInterface $routeResolver,
        RouteInterface $route,
        string $path,
    ): ?string {
        $label = sprintf('%s OPTIONS %s', $route->getName() ?? '(unnamed)', $path);

        $routeMismatch = $this->findRouteMismatch($routeResolver, $route, 'OPTIONS', $path, $label);
        if ($routeMismatch !== null) {
            return $routeMismatch;
        }

        $_SESSION = [];
        try {
            $response = $this->getTestApp(false)->handle($this->createRequest($path, 'OPTIONS'));
        } catch (Throwable $exception) {
            return sprintf('%s: threw %s: %s', $label, $exception::class, $exception->getMessage());
        }

        $status = $response->getStatusCode();
        $body = (string)$response->getBody();
        if ($status === 200 && $body === '') {
            return null;
        }

        return sprintf('%s: got %d, body "%s"', $label, $status, $body);
    }
}
