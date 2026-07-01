<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\BadgesAllowedOnlyMiddleware;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Tests\AppTestCase;

class BadgesAllowedOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsToAdminDashboardWhenBadgesAreNotAllowed(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        // korbo event type has badge generation disabled
        $connection->query('UPDATE event SET event_type = %s WHERE id = %i', 'korbo', 1);
        try {
            $event = $eventRepository->get(1);

            /** @var BadgesAllowedOnlyMiddleware $middleware */
            $middleware = $container->get(BadgesAllowedOnlyMiddleware::class);
            $handler = $this->createHandlerSpy();

            $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

            self::assertFalse($handler->called);
            self::assertSame(302, $response->getStatusCode());
            $expectedLocation = $app->getRouteCollector()->getRouteParser()
                ->urlFor('admin-dashboard', ['eventSlug' => $event->slug]);
            self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
        } finally {
            $this->resetEventToDefault($container);
        }
    }

    public function testPassesThroughWhenEventTypeAllowsBadges(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        // default event type has badge generation allowed
        $this->resetEventToDefault($container);
        $event = $eventRepository->get(1);

        /** @var BadgesAllowedOnlyMiddleware $middleware */
        $middleware = $container->get(BadgesAllowedOnlyMiddleware::class);
        $handler = $this->createHandlerSpy();

        $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @param App<ContainerInterface|null> $app
     */
    private function createRequestWithRouting(App $app, Event $event): ServerRequestInterface
    {
        $path = '/v2/event/' . $event->slug . '/admin/export/badges';

        return $this->createRequest($path)
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'GET'))
            ->withAttribute('event', $event);
    }

    private function createHandlerSpy(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public bool $called = false;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->called = true;

                return new Response();
            }
        };
    }
}
