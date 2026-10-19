<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Middleware\BaseMiddleware;
use kissj\Middleware\LockedStatusOnlyMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Middleware\PaidCancelledStatusOnlyMiddleware;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AppTestCase;

class StatusOnlyMiddlewareTest extends AppTestCase
{
    /**
     * @return iterable<string, array{class-string<BaseMiddleware>}>
     */
    public static function middlewareProvider(): iterable
    {
        yield 'open' => [OpenStatusOnlyMiddleware::class];
        yield 'locked' => [LockedStatusOnlyMiddleware::class];
        yield 'paid or cancelled' => [PaidCancelledStatusOnlyMiddleware::class];
    }

    /**
     * @return iterable<string, array{class-string<BaseMiddleware>, string, string, string}>
     */
    public static function rejectedStatusProvider(): iterable
    {
        $openLog = 'User is trying to change data without open status';
        $lockedLog = 'User tried to access after-lock edit without locked status';
        $paidOrCancelledLog = 'User is trying to access "paid or cancelled only" route'
            . ' without paid or cancelled status';
        $openFlash = 'flash.error.openStatusRequired';
        $lockedFlash = 'flash.error.lockedStatusRequired';
        $paidOrCancelledFlash = 'flash.error.paidOrCancelledStatusRequired';

        yield 'open rejects closed' => [OpenStatusOnlyMiddleware::class, 'closed', $openFlash, $openLog];
        yield 'open rejects paid' => [OpenStatusOnlyMiddleware::class, 'paid', $openFlash, $openLog];
        yield 'locked rejects open' => [LockedStatusOnlyMiddleware::class, 'open', $lockedFlash, $lockedLog];
        yield 'locked rejects cancelled' => [LockedStatusOnlyMiddleware::class, 'cancelled', $lockedFlash, $lockedLog];
        yield 'paid or cancelled rejects approved' => [
            PaidCancelledStatusOnlyMiddleware::class,
            'approved',
            $paidOrCancelledFlash,
            $paidOrCancelledLog,
        ];
    }

    /**
     * @return iterable<string, array{class-string<BaseMiddleware>, string}>
     */
    public static function allowedStatusProvider(): iterable
    {
        yield 'open allows open' => [OpenStatusOnlyMiddleware::class, 'open'];
        yield 'locked allows closed' => [LockedStatusOnlyMiddleware::class, 'closed'];
        yield 'locked allows approved' => [LockedStatusOnlyMiddleware::class, 'approved'];
        yield 'locked allows paid' => [LockedStatusOnlyMiddleware::class, 'paid'];
        yield 'paid or cancelled allows paid' => [PaidCancelledStatusOnlyMiddleware::class, 'paid'];
        yield 'paid or cancelled allows cancelled' => [PaidCancelledStatusOnlyMiddleware::class, 'cancelled'];
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('middlewareProvider')]
    public function testRedirectsToLoginAskEmailWhenNoUser(string $middlewareClass): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, null), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'loginAskEmail', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.warning.notLogged'), $this->flashed($app));
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('rejectedStatusProvider')]
    public function testRedirectsToDashboardWithFlashWhenStatusIsNotAllowed(
        string $middlewareClass,
        string $status,
        string $flashKey,
        string $logMessage,
    ): void {
        $this->expectLog(Level::Warning, $logMessage);
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->createUser($app, $event, UserStatus::from($status));
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, $user), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, $flashKey), $this->flashed($app));
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('allowedStatusProvider')]
    public function testPassesThroughWhenStatusIsAllowed(string $middlewareClass, string $status): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->createUser($app, $event, UserStatus::from($status));
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, $user), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $this->flashed($app));
    }

    public function testDoubleSubmittedCloseRegistrationRedirectsClosedUserWithFlash(): void
    {
        $this->expectLog(Level::Warning, 'User is trying to change data without open status');
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('double-close-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');
        $userService->setUserClosed($user);
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->getTestApp(false)->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/closeRegistration', 'POST'),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.error.openStatusRequired'), $this->flashed($app));
        self::assertSame('closed', $this->getService($app, UserRepository::class)->get($user->id)->status->value);
    }
}
