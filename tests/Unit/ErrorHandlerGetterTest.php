<?php

declare(strict_types=1);

namespace Tests\Unit;

use kissj\ErrorHandlerGetter;
use PHPUnit\Framework\TestCase;

class ErrorHandlerGetterTest extends TestCase
{
    public function testRenderShowsConfigErrorMessageOnExceptionPage(): void
    {
        $html = ErrorHandlerGetter::renderExceptionPage(
            'Configuration error: One or more environment variables failed assertions: DATABASE_HOST is missing.',
        );

        self::assertStringContainsString('Internal Server Error', $html);
        self::assertStringContainsString(
            '<p class="error-code">Configuration error: '
            . 'One or more environment variables failed assertions: DATABASE_HOST is missing.</p>',
            $html,
        );
    }

    public function testRenderEscapesHtmlInMessage(): void
    {
        $html = ErrorHandlerGetter::renderExceptionPage('<script>alert(1)</script>');

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testRenderWithoutMessageKeepsPageIntact(): void
    {
        $html = ErrorHandlerGetter::renderExceptionPage();

        self::assertStringStartsWith('<!DOCTYPE html>', $html);
        self::assertStringContainsString(
            "/: error 500 :/</p>\n\t<p class=\"error-code\"></p>\n</div>\n</body>",
            $html,
        );
        self::assertStringNotContainsString('Configuration error', $html);
    }
}
