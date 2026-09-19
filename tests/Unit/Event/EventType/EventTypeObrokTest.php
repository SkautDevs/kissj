<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Obrok\EventTypeObrok;
use PHPUnit\Framework\TestCase;

class EventTypeObrokTest extends TestCase
{
    public function testUsesIstCodeScript(): void
    {
        self::assertSame(
            'obrok27/istCode.js',
            (new EventTypeObrok())->getScriptNameWithoutLeadingSlash(),
        );
    }

    // _layout.twig loads this by name off the filesystem, so a typo would only surface as a 404 in production
    public function testIstCodeScriptFileExists(): void
    {
        // is_file, because assertFileExists also passes for the bare public/ directory
        self::assertTrue(
            is_file(__DIR__ . '/../../../../public/' . (new EventTypeObrok())->getScriptNameWithoutLeadingSlash()),
        );
    }
}
