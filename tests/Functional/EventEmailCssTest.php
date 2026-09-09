<?php

declare(strict_types=1);

namespace Tests\Functional;

use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Tests\AppTestCase;

class EventEmailCssTest extends AppTestCase
{
    private ?ContainerInterface $containerForCleanup = null;

    protected function tearDown(): void
    {
        if ($this->containerForCleanup !== null) {
            $this->resetEventToDefault($this->containerForCleanup);
            $this->containerForCleanup = null;
        }

        parent::tearDown();
    }

    public function testKorboEventEmailEmbedsItsCssIntoTheStyleBlock(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->containerForCleanup = $container;
        $this->setEventType($container, 'korbo', 'test-event-slug');

        $htmlBody = $this->captureLoginEmailHtmlBody($app);

        $styleStart = strpos($htmlBody, '<style>');
        $styleEnd = strpos($htmlBody, '</style>');
        self::assertIsInt($styleStart);
        self::assertIsInt($styleEnd);
        $styleBlock = substr($htmlBody, $styleStart, $styleEnd - $styleStart);

        // Korbo's brand color lands inside the style block itself, not a URL pointing at a file
        self::assertStringContainsString('#dd6700', $styleBlock);

        // eventEmailCss must be the last rule in <style>, so event rules override base rules
        // of equal specificity - assert its position rather than trusting the twig source order
        $footerLogoPosition = strpos($styleBlock, '.footer-logo');
        $korboBrandColorPosition = strpos($styleBlock, '#dd6700');
        self::assertIsInt($footerLogoPosition);
        self::assertIsInt($korboBrandColorPosition);
        self::assertGreaterThan($footerLogoPosition, $korboBrandColorPosition);

        // narrow guard against a hardcoded <link>: this harness builds requests with an empty
        // scheme/host (see AppTestCase::createRequest), so it cannot produce the resolvable
        // absolute URL a URL-based stylesheet link would need, even if one were reintroduced
        self::assertStringNotContainsString('<link rel="stylesheet"', $htmlBody);
    }

    public function testDefaultEventEmailCarriesNoEventCss(): void
    {
        $app = $this->getTestApp();

        $htmlBody = $this->captureLoginEmailHtmlBody($app);

        self::assertStringNotContainsString('#dd6700', $htmlBody);
        self::assertStringNotContainsString('<link rel="stylesheet"', $htmlBody);
    }

    public function testEmailShellStylesAreDefined(): void
    {
        $app = $this->getTestApp();

        $htmlBody = $this->captureLoginEmailHtmlBody($app);

        // these four classes are used by the templates - before this they were defined nowhere
        self::assertStringContainsString('max-width: 600px', $htmlBody);
        self::assertStringContainsString('.text-center {', $htmlBody);
        self::assertStringContainsString('.footer-item', $htmlBody);
        self::assertStringContainsString('.email-body', $htmlBody);
        // the footer is outside .container, so it needs its own centering to match the column
        self::assertStringContainsString('footer {', $htmlBody);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function captureLoginEmailHtmlBody(App $app): string
    {
        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $messageLogger = new MessageLoggerListener();
        $dispatcher->addSubscriber($messageLogger);

        // unique per run - avoids colliding with an email another test run already registered
        $email = 'eventcss.' . bin2hex(random_bytes(4)) . '@example.com';
        $app->handle($this->createRequest(
            '/v2/event/test-event-slug/login',
            'POST',
            ['email' => $email],
        ));

        $events = $messageLogger->getEvents()->getEvents();
        self::assertCount(1, $events);
        $message = $events[0]->getMessage();
        self::assertInstanceOf(TemplatedEmail::class, $message);

        $htmlBody = $message->getHtmlBody();
        self::assertIsString($htmlBody);

        return $htmlBody;
    }
}
