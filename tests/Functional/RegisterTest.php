<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

class RegisterTest extends AppTestCase
{
    public function testRegisterAndLogin(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $testEvent = $eventRepository->get(1);

        $email = 'register-test@example.com';
        $user = $userService->registerEmailUser($email, $testEvent);

        // Create a login token directly (bypassing email sending which needs full URL generation)
        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        // Test that we can retrieve the user via the token
        $loadedToken = $userService->getLoginTokenFromStringToken($loginToken->token);
        $loadedUser = $loadedToken->user;

        self::assertEquals($user->id, $loadedUser->id);
        self::assertFalse($loadedToken->used);
    }

    public function testLoginViaEmailToken(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $testEvent = $eventRepository->get(1);

        $email = 'login-via-email-test@example.com';

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $testEvent->slug . '/login',
            'POST',
            ['email' => $email],
        ));
        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('loginAfterLinkSent', $response->getHeaderLine('Location'));

        $user = $userRepository->getUserFromEmail($email, $testEvent);
        $tokens = $loginTokenRepository->findAllNonusedTokens($user);
        self::assertCount(1, $tokens);
        self::assertTrue($userService->isLoginTokenValid($tokens[0]->token));
    }
}
