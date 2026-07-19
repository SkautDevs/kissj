<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Middleware\PaidStatusOnlyMiddleware;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteContext;
use Tests\AppTestCase;

class PaidStatusOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsToDashboardWhenUserIsNotPaid(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->makeUserWithStatus($app, $event, UserStatus::Open);

        $middleware = $this->getService($app, PaidStatusOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->requestFor($app, $event, $user), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        $expectedLocation = $app->getRouteCollector()->getRouteParser()
            ->urlFor('dashboard', ['eventSlug' => $event->slug]);
        self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
    }

    public function testPassesThroughWhenUserIsPaid(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->makeUserWithStatus($app, $event, UserStatus::Paid);

        $middleware = $this->getService($app, PaidStatusOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->requestFor($app, $event, $user), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makeUserWithStatus(App $app, Event $event, UserStatus $status): User
    {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);

        $user = $userService->registerEmailUser('paid-guard-' . uniqid('', true) . '@example.com', $event);
        $user->status = $status;
        $userRepository->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function requestFor(App $app, Event $event, User $user): ServerRequestInterface
    {
        $path = '/v2/event/' . $event->slug . '/participant/dashboard';

        return $this->createRequest($path)
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'GET'))
            ->withAttribute('event', $event)
            ->withAttribute('user', $user);
    }
}
