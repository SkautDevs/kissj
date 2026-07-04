<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

class InvalidLoginTokenTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testFindLoginTokenByStringTokenReturnsRecordWhenFound(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->get(1);
        $user = $userService->registerEmailUser('find-existing@example.com', $event);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        $found = $userService->findLoginTokenByStringToken($loginToken->token);

        self::assertNotNull($found);
        self::assertSame($user->id, $found->user->id);
    }

    public function testFindLoginTokenByStringTokenReturnsNullWhenNotFound(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);

        self::assertNull($userService->findLoginTokenByStringToken('does-not-exist-' . bin2hex(random_bytes(8))));
    }

    public function testUsedTokenPrefillsEmailInSession(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->get(1);
        $user = $userService->registerEmailUser('expired-link@example.com', $event);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = true;
        $loginTokenRepository->persist($loginToken);

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/tryLogin/' . $loginToken->token),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/login', $response->getHeaderLine('Location'));
        self::assertSame('expired-link@example.com', $_SESSION['prefill_email'] ?? null);
    }

    public function testUnknownTokenDoesNotSetPrefillEmail(): void
    {
        $app = $this->getTestApp();

        $unknownToken = 'unknown-' . bin2hex(random_bytes(8));

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/tryLogin/' . $unknownToken),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertArrayNotHasKey('prefill_email', $_SESSION);
    }

    public function testValidTokenLogsInWithoutSettingPrefillEmail(): void
    {
        $app = $this->getTestApp();

        $userService = $this->getService($app, UserService::class);
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->get(1);
        $user = $userService->registerEmailUser('happy-path@example.com', $event);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/tryLogin/' . $loginToken->token),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertArrayNotHasKey('prefill_email', $_SESSION);
    }

    public function testLoginPageRendersPrefilledEmailFromSessionAndClearsIt(): void
    {
        $app = $this->getTestApp();

        $_SESSION['prefill_email'] = 'prefill-me@example.com';

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/login'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            'value="prefill-me@example.com"',
            (string) $response->getBody(),
        );
        self::assertArrayNotHasKey('prefill_email', $_SESSION);
    }

    public function testLoginPageRendersEmptyEmailFieldWhenNoSessionPrefill(): void
    {
        $app = $this->getTestApp();

        $response = $app->handle(
            $this->createRequest(self::BASE_URL . '/login'),
        );

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('id="form-email"', $body);
        self::assertStringContainsString('value=""', $body);
    }
}
