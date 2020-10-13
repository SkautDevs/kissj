<?php

namespace Tests\Functional;

use Tests\AppTestCase;

class ScenarioTest extends AppTestCase {
    public function testRunApp() {
        $appRoot = $this->getTestApp();

        // first time login
        $responseRoot = $appRoot->handle($this->createRequest('/'));
        $this->assertSame(301, $responseRoot->getStatusCode());
        $responseLandingHeader = $responseRoot->getHeaderLine('location');

        $appLanding = $this->getTestApp(false);
        $responseLanding = $appLanding->handle($this->createRequest($responseLandingHeader));
        $this->assertSame(302, $responseLanding->getStatusCode());
        $responseLoginHeader = $responseLanding->getHeaderLine('location');

        $appLogin = $this->getTestApp(false);
        $responseLogin = $appLogin->handle($this->createRequest($responseLoginHeader));
        $this->assertSame(200, $responseLogin->getStatusCode());
        $responseLoginBody = (string)$responseLogin->getBody();
        $formItem = 'id="form-email"';
        $this->assertStringContainsStringIgnoringCase($formItem, $responseLoginBody);

        $appPostLogin = $this->getTestApp(false);
        $loginParameters = ['email' => 'test%40test.com'];
        $responsePostLogin = $appPostLogin->handle($this->createRequest('/v2/kissj/login', 'POST', $loginParameters));
        $this->assertEquals(302, $responsePostLogin->getStatusCode());
        $responsePostLoginHeader = $responsePostLogin->getHeaderLine('location');

        $appLogin = $this->getTestApp(false);
        $responseFinalAfterLogin = $appLogin->handle($this->createRequest($responsePostLoginHeader));
        $this->assertSame(200, $responseFinalAfterLogin->getStatusCode());
        $responseFinalAfterLoginBody = (string)$responseFinalAfterLogin->getBody();
        $messagePostLogin = 'E-mail sent! Follow the link in it to log in.';
        $this->assertStringContainsString($messagePostLogin, $messagePostLogin);

        // editing values


        // logout


        // second time login

    }
}
