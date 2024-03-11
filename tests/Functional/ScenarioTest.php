<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\AppTestCase;

class ScenarioTest extends AppTestCase
{
    private const string TEST_EVENT_PREFIX_URL = '/v2/event/test-event-slug/';

    public function testRunApp(): void
    {
        // redirect to login
        $responseRoot = $this->getTestApp()->handle($this->createRequest(self::TEST_EVENT_PREFIX_URL));
        $this->assertSame(302, $responseRoot->getStatusCode());
        $responseLandingHeader = $responseRoot->getHeaderLine('location');

        // show login
        $responseLogin = $this->getTestApp(false)->handle($this->createRequest($responseLandingHeader));
        $this->assertSame(200, $responseLogin->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('id="form-email"', (string)$responseLogin->getBody());

        // send login
        $responsePostLogin = $this->getTestApp(false)->handle($this->createRequest(
            self::TEST_EVENT_PREFIX_URL . 'login',
            'POST',
            ['email' => 'test@examnple.com'],
        ));
        $this->assertEquals(302, $responsePostLogin->getStatusCode());
        $responsePostLoginHeader = $responsePostLogin->getHeaderLine('location');

        // show after login page
        $responseFinalAfterLogin = $this->getTestApp(false)->handle($this->createRequest($responsePostLoginHeader));
        $this->assertSame(200, $responseFinalAfterLogin->getStatusCode());
        $this->assertStringContainsString(
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
