<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class DefaultEventLoginPageTest extends AppTestCase
{
    public function testLoginPageOffersOnlySkautisLogin(): void
    {
        $app = $this->getTestApp();

        $response = $app->handle($this->createRequest('/v2/event/test-event-slug/login'));
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Přihlásit se přes skautIS', $html);
        self::assertStringNotContainsString('name="email"', $html);
    }

    public function testLoginPageStaysCzechWhenEnglishIsRequested(): void
    {
        $app = $this->getTestApp();

        $request = $this->createRequest('/v2/event/test-event-slug/login')
            ->withQueryParams(['locale' => 'en'])
            ->withHeader('Accept-Language', 'en');
        $html = (string)$app->handle($request)->getBody();

        self::assertStringContainsString('Registrace pro ', $html);
        self::assertStringNotContainsString('Registration for', $html);
        self::assertStringNotContainsString('?locale=en', $html);
    }
}
