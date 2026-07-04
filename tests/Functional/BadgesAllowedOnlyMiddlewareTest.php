<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\BadgesAllowedOnlyMiddleware;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Tests\AppTestCase;

class BadgesAllowedOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsToAdminDashboardWhenBadgesAreNotAllowed(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        // korbo event type has badge generation disabled
        $connection->query('UPDATE event SET event_type = %s WHERE id = %i', 'korbo', 1);
        try {
            $event = $eventRepository->get(1);

            $middleware = $this->getService($app, BadgesAllowedOnlyMiddleware::class);
            $handler = new RequestHandlerSpy();

            $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

            self::assertFalse($handler->called);
            self::assertSame(302, $response->getStatusCode());
            $expectedLocation = $app->getRouteCollector()->getRouteParser()
                ->urlFor('admin-dashboard', ['eventSlug' => $event->slug]);
            self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
        } finally {
            $this->resetEventToDefault($app->getContainer());
        }
    }

    public function testPassesThroughWhenEventTypeAllowsBadges(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);

        // default event type has badge generation allowed
        $this->resetEventToDefault($app->getContainer());
        $event = $eventRepository->get(1);

        $middleware = $this->getService($app, BadgesAllowedOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createRequestWithRouting(App $app, Event $event): ServerRequestInterface
    {
        $path = '/v2/event/' . $event->slug . '/admin/export/badges';

        return $this->createRequest($path)
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'GET'))
            ->withAttribute('event', $event);
    }
}
