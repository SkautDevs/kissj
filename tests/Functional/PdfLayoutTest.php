<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Patrol\PatrolsRoster;
use Slim\Views\Twig;
use Tests\AppTestCase;

class PdfLayoutTest extends AppTestCase
{
    public function testPdfLayoutInlinesCssInsteadOfLinkingStylesheets(): void
    {
        $app = $this->getTestApp();
        $twig = $this->getService($app, Twig::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);

        $html = $twig->fetch('roster/roster.twig', [
            'event' => $event,
            'patrolsRoster' => new PatrolsRoster([]),
            'pdfCss' => '.pdf-css-marker { color: red; }',
        ]);

        // mPDF cannot fetch <link> stylesheets, so the layout must inline the CSS
        self::assertStringContainsString('.pdf-css-marker', $html);
        self::assertStringNotContainsString('<link rel="stylesheet"', $html);
    }
}
