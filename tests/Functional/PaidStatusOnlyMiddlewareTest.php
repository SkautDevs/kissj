<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Middleware\PaidStatusOnlyMiddleware;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class PaidStatusOnlyMiddlewareTest extends AppTestCase
{
    public function testRedirectsToLoginAskEmailWhenNoUser(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);

        $middleware = $this->getService($app, PaidStatusOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRoutedUserRequest($app, $event, null), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        $expectedLocation = $app->getRouteCollector()->getRouteParser()
            ->urlFor('loginAskEmail', ['eventSlug' => $event->slug]);
        self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
    }

    public function testRedirectsToDashboardWhenUserIsNotPaid(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->createUser($app, $event, UserStatus::Approved);

        $middleware = $this->getService($app, PaidStatusOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRoutedUserRequest($app, $event, $user), $handler);

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
        $user = $this->createUser($app, $event, UserStatus::Paid);

        $middleware = $this->getService($app, PaidStatusOnlyMiddleware::class);
        $handler = new RequestHandlerSpy();

        $response = $middleware->process($this->createRoutedUserRequest($app, $event, $user), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }
}
