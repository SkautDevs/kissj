<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class ScenarioTest extends AppTestCase
{
    private const string TEST_EVENT_PREFIX_URL = '/v2/event/test-event-slug/';

    public function testRunApp(): void
    {
        // redirect from landing to login (via middleware chain)
        $app = $this->getTestApp();

        $responseRoot = $app->handle($this->createRequest(self::TEST_EVENT_PREFIX_URL));
        self::assertSame(302, $responseRoot->getStatusCode());
        $responseLoginHeader = $responseRoot->getHeaderLine('location');

        // show login page
        $responseLogin = $this->getTestApp(false)->handle($this->createRequest($responseLoginHeader));
        self::assertSame(200, $responseLogin->getStatusCode());
        self::assertStringContainsStringIgnoringCase('id="form-email"', (string)$responseLogin->getBody());

        // send login
        $responsePostLogin = $this->getTestApp(false)->handle($this->createRequest(
            self::TEST_EVENT_PREFIX_URL . 'login',
            'POST',
            ['email' => 'test@examnple.com'],
        ));
        self::assertEquals(302, $responsePostLogin->getStatusCode());
        $responsePostLoginHeader = $responsePostLogin->getHeaderLine('location');

        // show after login page
        $responseFinalAfterLogin = $this->getTestApp(false)->handle($this->createRequest($responsePostLoginHeader));
        self::assertSame(200, $responseFinalAfterLogin->getStatusCode());
        self::assertStringContainsString(
            'E-mail poslán! Přihlaš se pomocí odkazu v něm',
            (string)$responseFinalAfterLogin->getBody(),
        );

        // TODO catch email and follow link

        // choose role

        // edit values

        // logout

        // second time login

        // lock registration
    }
}
