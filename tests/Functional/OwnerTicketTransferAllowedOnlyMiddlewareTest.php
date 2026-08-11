<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Middleware\OwnerTicketTransferAllowedOnlyMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class OwnerTicketTransferAllowedOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsWhenOwnerTransferIsNotAllowed(): void
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

        $translator = $this->getService($app, TranslatorInterface::class);
        $flashed = array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
        self::assertContains($translator->trans('flash.error.ownerTransferNotAllowed'), $flashed);
    }

    public function testPassesThroughWhenEventTypeAllowsOwnerTransfer(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $middleware = $this->getService($app, OwnerTicketTransferAllowedOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsOnceEventHasStarted(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getOwnerTransferEvent($app);
        $this->mutateEventForTest($container, $event, [
            'startDay' => DateTimeUtils::getDateTime('-1 day'),
        ]);

        $middleware = $this->getService($app, OwnerTicketTransferAllowedOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRequestWithRouting($app, $event), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        $expectedLocation = $app->getRouteCollector()->getRouteParser()
            ->urlFor('dashboard', ['eventSlug' => $event->slug]);
        self::assertSame($expectedLocation, $response->getHeaderLine('Location'));

        $translator = $this->getService($app, TranslatorInterface::class);
        $flashed = array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
        self::assertContains($translator->trans('flash.error.ownerTransferAfterEventStart'), $flashed);
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
