<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\User\UserService;
use Tests\AppTestCase;

class ObrokChangeDetailsRenderTest extends AppTestCase
{
    private const string BASE_URL = '/v2/event/obrok37';

    public function testIstFormRendersHelpTexts(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-helptext-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/showChangeDetails'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('dostanou vrácenou část poplatku', $body);
        self::assertStringContainsString('Chceš dělat buddyho zahraničním servisákům?', $body);
        self::assertStringContainsString('Bereš s sebou dítě do školky?', $body);
        self::assertStringContainsString('href="https://obrok.skaut.cz/souhlas"', $body);
        self::assertStringContainsString('PDF nebo obrázek, max 10 MB', $body);
        self::assertStringNotContainsString('PDF s potvrzením najdeš na našem webu', $body);
        self::assertSame(1, substr_count($body, 'v případě jiné diety'));
    }

    public function testLayoutWrapsEventNameAndMarksHelpTexts(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-layout-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/showChangeDetails'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString(
            '<h1 class="text-center">Registrace <span class="event-name">'
                . htmlspecialchars($event->readableName) . '</span></h1>',
            $body,
        );
        self::assertStringContainsString('<link rel="icon" href="/obrok27/favicon-32.png" type="image/png">', $body);
        self::assertStringContainsString(
            '<link rel="apple-touch-icon" href="/obrok27/apple-touch-icon-180.png">',
            $body,
        );
        self::assertStringContainsString(
            '<meta name="theme-color" content="#F1F1F4" media="(prefers-color-scheme: light)">',
            $body,
        );
        self::assertStringContainsString(
            '<meta name="theme-color" content="#15133F" media="(prefers-color-scheme: dark)">',
            $body,
        );
        self::assertStringContainsString('<div class="help-text">', $body);
        self::assertStringNotContainsString('<div>PDF nebo obrázek, max 10 MB', $body);
    }
}
