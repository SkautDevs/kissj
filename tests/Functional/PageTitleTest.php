<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class PageTitleTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testLoginPageUsesItsOwnTitle(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $translator = $this->getService($app, TranslatorInterface::class);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/login'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString(
            '<title>' . $translator->trans('pageTitle.login') . ' - ' . $event->readableName . ' - kissj</title>',
            $body,
        );
    }

    public function testPageTitleIsAlsoUsedInHeading(): void
    {
        $app = $this->getTestApp();
        $translator = $this->getService($app, TranslatorInterface::class);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/loginHelp'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $title = $translator->trans('pageTitle.loginHelp');
        self::assertStringContainsString('<h1 class="text-center">' . $title, $body);
        // the generic layout title must no longer be the heading on a page with its own title
        self::assertStringNotContainsString('<h1 class="text-center">' . $translator->trans('_layout.title'), $body);
    }

    public function testPagesWithoutTitleKeepLayoutDefault(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $translator = $this->getService($app, TranslatorInterface::class);

        $response = $app->handle($this->createRequest(self::BASE_URL));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            '<title>' . $translator->trans('_layout.title') . ' - ' . $event->readableName . ' - kissj</title>',
            (string) $response->getBody(),
        );
    }

    public function testTitleRendersWithoutEvent(): void
    {
        $app = $this->getTestApp();
        $translator = $this->getService($app, TranslatorInterface::class);

        // the error handler renders 404.twig outside any event context, so the layout
        // has no event global to append - it must still produce a title
        $body = $this->getService($app, Twig::class)->fetch('404.twig');

        self::assertStringContainsString(
            '<title>' . $translator->trans('pageTitle.notFound') . ' - kissj</title>',
            $body,
        );
    }
}
