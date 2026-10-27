<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Tests\AppTestCase;

class ScenarioTest extends AppTestCase
{
    private const string TEST_EVENT_PREFIX_URL = '/v2/event/test-event-slug/';

    public function testRunApp(): void
    {
        // redirect from landing to login (via middleware chain)
        $app = $this->getTestApp();
        $this->setEventType($app->getContainer(), 'miquik', 'test-event-slug');

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
        self::assertSame(302, $responsePostLogin->getStatusCode());
        $responsePostLoginHeader = $responsePostLogin->getHeaderLine('location');

        // show after login page
        $responseFinalAfterLogin = $this->getTestApp(false)->handle($this->createRequest($responsePostLoginHeader));
        self::assertSame(200, $responseFinalAfterLogin->getStatusCode());
        self::assertStringContainsString(
            'E-mail poslán! Přihlaš se pomocí odkazu v něm',
            (string)$responseFinalAfterLogin->getBody(),
        );

        $email = 'scenario.' . bin2hex(random_bytes(4)) . '@example.com';
        $firstName = 'Scenario' . bin2hex(random_bytes(2));

        $loginMail = $this->requestLoginMail($email);
        $responseTryLogin = $this->sendRequest($this->extractTokenPath($loginMail));
        self::assertSame(302, $responseTryLogin->getStatusCode());

        $responseChooseRole = $this->followRedirects($responseTryLogin);
        self::assertSame(200, $responseChooseRole->getStatusCode());
        self::assertStringContainsString('name="role" value="ist"', (string)$responseChooseRole->getBody());

        $responseSetRole = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'setRole', 'POST', ['role' => 'ist']);
        self::assertSame(302, $responseSetRole->getStatusCode());

        $responseDetailsForm = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'participant/showChangeDetails');
        self::assertSame(200, $responseDetailsForm->getStatusCode());
        self::assertStringContainsString('name="firstName"', (string)$responseDetailsForm->getBody());

        $responseChangeDetails = $this->sendRequest(
            self::TEST_EVENT_PREFIX_URL . 'participant/changeDetails',
            'POST',
            [
                'firstName' => $firstName,
                'lastName' => 'Tester',
                'gender' => 'man',
                'birthDate' => '1995-05-15',
                'permanentResidence' => '1 Scout Street, Scout Town',
            ],
        );
        self::assertSame(302, $responseChangeDetails->getStatusCode());

        $responseDashboard = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'participant/dashboard');
        self::assertSame(200, $responseDashboard->getStatusCode());
        self::assertStringContainsString($firstName, (string)$responseDashboard->getBody());
        self::assertStringContainsString('participant/closeRegistration', (string)$responseDashboard->getBody());

        $responseCloseForm = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'participant/closeRegistration');
        self::assertSame(200, $responseCloseForm->getStatusCode());

        $responseClose = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'participant/closeRegistration', 'POST');
        self::assertSame(302, $responseClose->getStatusCode());

        $app = $this->getTestApp(false);
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->getService($app, UserRepository::class)->getUserFromEmail($email, $event);
        self::assertSame(UserStatus::Closed, $user->status);

        $responseLockedDashboard = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'participant/dashboard');
        self::assertSame(200, $responseLockedDashboard->getStatusCode());
        self::assertStringContainsString($firstName, (string)$responseLockedDashboard->getBody());
        self::assertStringNotContainsString(
            'participant/closeRegistration',
            (string)$responseLockedDashboard->getBody(),
        );

        $responseLogout = $this->sendRequest(self::TEST_EVENT_PREFIX_URL . 'logout');
        self::assertSame(302, $responseLogout->getStatusCode());

        $secondLoginMail = $this->requestLoginMail($email);
        $responseSecondTryLogin = $this->sendRequest($this->extractTokenPath($secondLoginMail));
        self::assertSame(302, $responseSecondTryLogin->getStatusCode());

        $responseSecondDashboard = $this->followRedirects($responseSecondTryLogin);
        self::assertSame(200, $responseSecondDashboard->getStatusCode());
        self::assertStringContainsString($firstName, (string)$responseSecondDashboard->getBody());
    }

    /**
     * @param array<string,string> $body
     */
    private function sendRequest(string $path, string $method = 'GET', array $body = []): ResponseInterface
    {
        return $this->getTestApp(false)->handle($this->createRequest($path, $method, $body));
    }

    private function followRedirects(ResponseInterface $response): ResponseInterface
    {
        for ($hops = 0; $response->getStatusCode() === 302; $hops++) {
            self::assertLessThan(5, $hops);
            $response = $this->sendRequest($response->getHeaderLine('location'));
        }

        return $response;
    }

    private function requestLoginMail(string $email): TemplatedEmail
    {
        $app = $this->getTestApp(false);
        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $messageLogger = new MessageLoggerListener();
        $dispatcher->addSubscriber($messageLogger);

        $loginRequest = $this->createRequest(self::TEST_EVENT_PREFIX_URL . 'login', 'POST', ['email' => $email]);
        $app->handle($loginRequest);

        $events = $messageLogger->getEvents()->getEvents();
        self::assertCount(1, $events);
        $message = $events[0]->getMessage();
        self::assertInstanceOf(TemplatedEmail::class, $message);

        return $message;
    }

    private function extractTokenPath(TemplatedEmail $message): string
    {
        $htmlBody = $message->getHtmlBody();
        self::assertIsString($htmlBody);
        self::assertSame(1, preg_match('~(/v2/event/[^/"\\s]+/tryLogin/[0-9a-f]+)~', $htmlBody, $matches));

        return $matches[1];
    }
}
