<?php

declare(strict_types=1);

namespace Tests\Functional;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Tests\AppTestCase;

class LoginEmailCaptureTest extends AppTestCase
{
    public function testLoginSendsTokenEmail(): void
    {
        $app = $this->getTestApp();

        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $messageLogger = new MessageLoggerListener();
        $dispatcher->addSubscriber($messageLogger);

        // unique per run - the functional test database persists between runs
        $email = 'capture.' . bin2hex(random_bytes(4)) . '@example.com';

        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/login',
            'POST',
            ['email' => $email],
        ));

        $events = $messageLogger->getEvents()->getEvents();
        self::assertCount(1, $events);

        $message = $events[0]->getMessage();
        self::assertInstanceOf(TemplatedEmail::class, $message);

        $recipients = $message->getTo();
        self::assertCount(1, $recipients);
        self::assertSame($email, $recipients[0]->getAddress());

        $subject = $message->getSubject();
        self::assertNotNull($subject);
        self::assertStringContainsString('test-event-readable-name', $subject);

        $htmlBody = $message->getHtmlBody();
        self::assertIsString($htmlBody);
        self::assertStringContainsString('/tryLogin/', $htmlBody);
    }
}
