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
}
