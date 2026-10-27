<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Middleware\BaseMiddleware;
use kissj\Middleware\PatrolLeadersOnlyMiddleware;
use kissj\Middleware\TechnicalTeamLeadersOnlyMiddleware;
use kissj\Middleware\TroopLeadersOnlyMiddleware;
use kissj\Middleware\TroopParticipantsOnlyMiddleware;
use kissj\User\UserService;
use kissj\User\UserStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AppTestCase;

class RoleOnlyMiddlewareTest extends AppTestCase
{
    /**
     * @return iterable<string, array{class-string<BaseMiddleware>, string}>
     */
    public static function matchingRoleProvider(): iterable
    {
        foreach (self::mismatchProvider() as $name => [$middlewareClass, $role]) {
            yield $name => [$middlewareClass, $role];
        }
    }

    /**
     * @return iterable<string, array{class-string<BaseMiddleware>}>
     */
    public static function middlewareProvider(): iterable
    {
        foreach (self::mismatchProvider() as $name => [$middlewareClass]) {
            yield $name => [$middlewareClass];
        }
    }

    /**
     * @return iterable<string, array{class-string<BaseMiddleware>, string, string, string}>
     */
    public static function mismatchProvider(): iterable
    {
        yield 'patrol leaders' => [PatrolLeadersOnlyMiddleware::class, 'pl', 'tl', 'flash.error.plOnly'];
        yield 'troop leaders' => [TroopLeadersOnlyMiddleware::class, 'tl', 'pl', 'flash.error.tlOnly'];
        yield 'technical team leaders' => [
            TechnicalTeamLeadersOnlyMiddleware::class,
            'ttl',
            'pl',
            'flash.error.ttlOnly',
        ];
        yield 'troop participants' => [TroopParticipantsOnlyMiddleware::class, 'tp', 'pl', 'flash.error.tpOnly'];
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('matchingRoleProvider')]
    public function testPassesThroughWhenRoleMatches(string $middlewareClass, string $role): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->createUser($app, $event, UserStatus::Open);
        $this->getService($app, UserService::class)->createParticipantSetRole($user, $role);
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, $user), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $this->flashed($app));
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('mismatchProvider')]
    public function testRedirectsToLandingWithFlashWhenRoleDiffers(
        string $middlewareClass,
        string $role,
        string $otherRole,
        string $flashKey,
    ): void {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->createUser($app, $event, UserStatus::Open);
        $this->getService($app, UserService::class)->createParticipantSetRole($user, $otherRole);
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, $user), $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'landing', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, $flashKey), $this->flashed($app));
    }

    /**
     * @param class-string<BaseMiddleware> $middlewareClass
     */
    #[DataProvider('middlewareProvider')]
    public function testPassesThroughWhenNoUser(string $middlewareClass): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $handler = new RequestHandlerSpy();

        $response = $this->getService($app, $middlewareClass)
            ->process($this->createRoutedUserRequest($app, $event, null), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $this->flashed($app));
    }
}
