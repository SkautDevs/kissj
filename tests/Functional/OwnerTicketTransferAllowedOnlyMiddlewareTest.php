<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\OwnerTicketTransferAllowedOnlyMiddleware;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Tests\AppTestCase;

class OwnerTicketTransferAllowedOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsToDashboardWhenOwnerTransferIsNotAllowed(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);

        // fresh DB seeds event 1 with the default event type, which disallows owner ticket transfer
        $event = $eventRepository->get(1);

        $middleware = $this->getService($app, OwnerTicketTransferAllowedOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        $expectedLocation = $app->getRouteCollector()->getRouteParser()
            ->urlFor('dashboard', ['eventSlug' => $event->slug]);
        self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
    }

    public function testPassesThroughWhenEventTypeAllowsOwnerTransfer(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        // korbo event type allows owner ticket transfer; the Event entity exposes only a
        // getter for event_type (no setter, no reverse slug mapping), so a raw update is the
        // only way to flip the type in a test without adding unused production code
        $connection->query('UPDATE event SET event_type = %s WHERE id = %i', 'korbo', 1);
        $event = $eventRepository->get(1);

        $middleware = $this->getService($app, OwnerTicketTransferAllowedOnlyMiddleware::class);
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
        $path = '/v2/event/' . $event->slug . '/participant/dashboard';

        return $this->createRequest($path)
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'GET'))
            ->withAttribute('event', $event);
    }
}
